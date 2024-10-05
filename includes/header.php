<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'IWT Project'; ?></title>
    <link rel="stylesheet" href="/jobpro/assets/css/styles.css">
    <link rel="stylesheet" href="/jobpro/assets/css/style.css">
    <link rel="stylesheet" href="/jobpro/assets/css/<?=$pagename?>.css">
    <!-- <link rel="stylesheet" href="/jobpro/assets/css/responsive.css"> -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <!-- <script src="jobpro/assets/js/script.js" defer></script> -->
</head>
<body>
    <header>
        <nav>
            <div class="logo">JobPro</div>
            <ul>
                <li><a href="/jobpro/index.php"><i class="fas fa-home"></i>Home</a></li>
                <li><a href="/jobpro/pages/createJob.php"><i class="fa-solid fa-circle-plus"></i>Post a Job</a></li>
                <li><a href="/jobpro/pages/seekJob.php"><i class="fas fa-search"></i>Seek Job</a></li>
                <li><a href="/jobpro/pages/about.php"><i class="fa-solid fa-circle-info"></i>About us</a></li>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <!-- <li><a href="/pages/logout.php">Logout</a></li> -->
                <?php else: ?>
                    <!-- <li><a href="/pages/login.php">Login</a></li> -->
                    <!-- <li><a href="/pages/register.php">Register</a></li> -->
                <?php endif; ?>
            </ul>
            <div class="auth-buttons">
            <?php  if (!isset($_SESSION['username'])) : ?>
                <a href="/jobpro/pages/login.php" class="sign-in">Log In</a>
                <a href="/jobpro/pages/register.php" class="create-account">Create Account</a>
            <?php else : ?>
                <a href="/jobpro/pages/account.php" class="sign-in"><i class="fas fa-user-circle"></i><strong><?php echo $_SESSION['username']; ?></strong></a>
                <a href="/jobpro/api/logout.php?logout=true" class="create-account">Logout</a>
            <?php endif; ?>
            </div>
        </nav>
    </header>
    <main>