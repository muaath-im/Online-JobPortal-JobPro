<?php
include 'dbconnect.php';

function getCompanyJobs($companyId) {
    global $mysqli;
    $query = "SELECT j.id, j.job_title, j.job_description, j.min_salary, j.max_salary, j.job_location, j.created_at
              FROM jobs j
              WHERE j.company_id = ?
              ORDER BY j.created_at DESC";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param('i', $companyId);
    $stmt->execute();
    $result = $stmt->get_result();
    $jobs = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $jobs;
}

function getUserData($userId) {
    global $mysqli;
    
    $stmt = $mysqli->prepare("
        SELECT 
            person.user_id, 
            full_name, 
            qualification, 
            year_of_experience, 
            location, 
            city, 
            zip_code, 
            country, 
            portfolio_link, 
            cv_link, 
            user.username,
            phone_number,
            email
        FROM person 
        JOIN user ON person.user_id = user.user_id 
        WHERE person.user_id = ?
    ");
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    
    $result = $stmt->get_result();
    $userData = $result->fetch_assoc();
    
    $stmt->close();
    
    return $userData;
}

function create($table, $data, $types = null) {
    global $mysqli;

    $columns = implode(", ", array_keys($data));
    $placeholders = implode(", ", array_fill(0, count($data), '?'));
    $values = array_values($data);

    $sql = "INSERT INTO $table ($columns) VALUES ($placeholders)";
    $stmt = $mysqli->prepare($sql);

    // If types are not provided, default to strings
    if ($types === null) {
        $types = str_repeat('s', count($values));
    }

    $stmt->bind_param($types, ...$values);

    if ($stmt->execute()) {
        return $mysqli->insert_id;
    } else {
        return false;
    }
}


function read($table, $conditions = [], $fields = '*', $limit = null, $orderBy = null) {
    global $mysqli;

    $sql = "SELECT $fields FROM $table";
    
    if (!empty($conditions)) {
        $sql .= " WHERE " . implode(' AND ', array_map(function($col) {
            return "$col = ?";
        }, array_keys($conditions)));
    }
    
    if ($orderBy) {
        $sql .= " ORDER BY $orderBy";
    }

    if ($limit) {
        $sql .= " LIMIT ?";
    }

    $stmt = $mysqli->prepare($sql);

    $bindValues = array_values($conditions);
    if ($limit) {
        $bindValues[] = $limit;
    }
    
    if (!empty($conditions) || $limit) {
        $types = str_repeat('s', count($conditions)) . ($limit ? 'i' : ''); // Assuming conditions are strings and limit is integer
        $stmt->bind_param($types, ...$bindValues);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->fetch_all(MYSQLI_ASSOC);
}

function update($table, $data, $conditions, $types = null) {
    global $mysqli;

    $setFields = implode(", ", array_map(function($key) {
        return "$key = ?";
    }, array_keys($data)));

    $conditionFields = implode(" AND ", array_map(function($key) {
        return "$key = ?";
    }, array_keys($conditions)));

    $sql = "UPDATE $table SET $setFields WHERE $conditionFields";
    $stmt = $mysqli->prepare($sql);

    $values = array_merge(array_values($data), array_values($conditions));

    // If types are not provided, default to strings
    if ($types === null) {
        $types = str_repeat('s', count($values)); // Assuming string types
    }

    $stmt->bind_param($types, ...$values);
    
    return $stmt->execute();
}


function delete($table, $conditions) {
    global $mysqli;

    $conditionFields = implode(" AND ", array_map(function($key) {
        return "$key = ?";
    }, array_keys($conditions)));

    $sql = "DELETE FROM $table WHERE $conditionFields";
    $stmt = $mysqli->prepare($sql);

    $types = str_repeat('s', count($conditions)); // Assuming string types
    $stmt->bind_param($types, ...array_values($conditions));

    if ($stmt->execute()) {
        return $stmt->affected_rows; // Return the number of affected rows
    } else {
        return false;
    }
}


