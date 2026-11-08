<?php
require_once jobproPath('src/Core/auth.php');
jobproStartSession();
$user = jobproRequireUser();
jobproPost('/pages/account.php?section=profile');

$role = $user['account_type'];
$data = [];
$errors = [];
$fields = ['email','phone','full_name','qualification','experience','location','city','zipcode','country','portfolio','cv','companyName','website','address'];
foreach ($fields as $field) $data[$field] = jobproInput($field);

if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL) || strlen($data['email']) > 255) $errors['email'] = 'Enter a valid email address.';
if (strlen($data['phone']) > 20) $errors['phone'] = 'Use up to 20 characters.';
$emailChanged = strcasecmp($data['email'], $user['email']) !== 0;
if ($emailChanged && !password_verify(jobproSecret('email_password'), $user['password'])) $errors['email_password'] = 'Enter your current password to change your email address.';
$required = $role === 'personal' ? 'full_name' : 'companyName';
if ($data[$required] === '') $errors[$required] = 'Enter your name.';
foreach (['full_name','qualification','location','city','portfolio','cv','companyName','website','address'] as $field) {
    if (mb_strlen($data[$field]) > 255) $errors[$field] = 'Use up to 255 characters.';
}
if (mb_strlen($data['country']) > 100) $errors['country'] = 'Use up to 100 characters.';
if (mb_strlen($data['zipcode']) > 20) $errors['zipcode'] = 'Use up to 20 characters.';
if ($role === 'personal' && $data['experience'] !== '' && (!ctype_digit($data['experience']) || (int)$data['experience'] > 80)) $errors['experience'] = 'Enter a whole number from 0 to 80.';

foreach ($role === 'personal' ? ['portfolio'] : ['website'] as $field) {
    if ($message = jobproUrlError($data[$field])) $errors[$field] = $message;
}
if ($role === 'personal' && $data['cv'] !== '' && !str_starts_with($data['cv'], '/storage/resumes/')) {
    if ($message = jobproUrlError($data['cv'])) $errors['cv'] = $message;
}

if ($errors) jobproFormFailure('profile', $errors, $data, '/pages/account.php?section=profile');

$newImage = null;
$newResume = null;
$db = null;
try {
    $hasUpload = !empty($_FILES['avatar']) && ($_FILES['avatar']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE;
    if (!empty($_POST['remove_avatar']) && $hasUpload) throw new InvalidArgumentException('Choose either a new image or removal of the current image.');
    $newImage = jobproUploadAvatar($_FILES['avatar'] ?? []);

    $hasResumeUpload = !empty($_FILES['resume']) && ($_FILES['resume']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE;
    if (!empty($_POST['remove_resume']) && $hasResumeUpload) throw new InvalidArgumentException('Choose either a new resume or removal of the current resume.');
    if ($hasResumeUpload) {
        $newResume = jobproUploadResume($_FILES['resume'], (int)$user['user_id']);
    }

    $db = jobproDatabase();
    $db->begin_transaction();
    $fresh = jobproUserById((int)$user['user_id'], true);
    if (!$fresh || !hash_equals($user['password'], $fresh['password']) || $fresh['account_type'] !== $role) throw new InvalidArgumentException('Your account access changed. Sign in again before saving your profile.');
    if (strcasecmp($data['email'], $fresh['email']) !== 0 && !password_verify(jobproSecret('email_password'), $fresh['password'])) throw new InvalidArgumentException('Your email changed in another request. Enter your current password and try again.');
    $oldImage = $fresh['profile_image'];
    $image = !empty($_POST['remove_avatar']) ? null : ($newImage ?? $oldImage);
    $user = $fresh;

    $stmt = $db->prepare('UPDATE user SET email=?,phone_number=?,profile_image=? WHERE user_id=?');
    $stmt->bind_param('sssi', $data['email'], $data['phone'], $image, $user['user_id']);
    $stmt->execute();
    $stmt->close();

    if ($role === 'personal') {
        $experience = $data['experience'] === '' ? null : (int)$data['experience'];
        if (empty($user['person_id'])) {
            $stmt = $db->prepare('INSERT INTO person (user_id,full_name) VALUES (?,?)');
            $stmt->bind_param('is', $user['user_id'], $data['full_name']);
            $stmt->execute();
            $_SESSION['person_id'] = $stmt->insert_id;
            $stmt->close();
        }

        $stmt = $db->prepare('SELECT cv_link FROM person WHERE user_id=?');
        $stmt->bind_param('i', $user['user_id']);
        $stmt->execute();
        $oldResume = $stmt->get_result()->fetch_assoc()['cv_link'] ?? null;
        $stmt->close();

        if (!empty($_POST['remove_resume'])) {
            $resumeValue = null;
        } elseif ($newResume !== null) {
            $resumeValue = $newResume;
        } elseif ($data['cv'] !== '') {
            $resumeValue = $data['cv'];
        } else {
            $resumeValue = $oldResume;
        }

        $stmt = $db->prepare('UPDATE person SET full_name=?,qualification=?,year_of_experience=?,location=?,city=?,zip_code=?,country=?,portfolio_link=?,cv_link=? WHERE user_id=?');
        $stmt->bind_param('ssissssssi', $data['full_name'], $data['qualification'], $experience, $data['location'], $data['city'], $data['zipcode'], $data['country'], $data['portfolio'], $resumeValue, $user['user_id']);
        $stmt->execute();
        $stmt->close();

        if ($resumeValue !== $oldResume && ($oldPath = jobproResumePath($oldResume))) {
            @unlink($oldPath);
        }
    } else {
        if (empty($user['company_id'])) {
            $stmt = $db->prepare('INSERT INTO company (user_id,company_name) VALUES (?,?)');
            $stmt->bind_param('is', $user['user_id'], $data['companyName']);
            $stmt->execute();
            $_SESSION['company_id'] = $stmt->insert_id;
            $stmt->close();
        }
        $stmt = $db->prepare('UPDATE company SET company_name=?,website_link=?,address=? WHERE user_id=?');
        $stmt->bind_param('sssi', $data['companyName'], $data['website'], $data['address'], $user['user_id']);
        $stmt->execute();
        $stmt->close();
    }
    $db->commit();
    if ($image !== $oldImage && ($path = jobproAvatarPath($oldImage))) unlink($path);
    jobproFlash('Your profile has been saved.');
    jobproRedirect('/pages/account.php?section=profile');
} catch (Throwable $error) {
    if ($db) $db->rollback();
    if ($newImage && ($path = jobproAvatarPath($newImage))) unlink($path);
    if ($newResume && ($path = jobproResumePath($newResume))) unlink($path);
    error_log('JobPro profile update failed (code ' . $error->getCode() . ').');
    $message = $error instanceof InvalidArgumentException ? $error->getMessage() : ($error->getCode() === 1062 ? 'That email is already in use.' : 'Your profile could not be saved. Please try again.');
    jobproFormFailure('profile', ['form' => $message], $data, '/pages/account.php?section=profile');
}
