<?php 
session_start(); 
include 'connect.php'; 
include 'header.php'; 

if (!isset($_SESSION['manager_id'])) { 
    header("Location: login.php"); 
    exit(); 
} 

$manager_id = $_SESSION['manager_id']; 

if (isset($_GET['id'])) { 
    $employee_id = $_GET['id']; 

    $stmt = $conn->prepare("SELECT * FROM employees WHERE id = ? AND manager_id = ?"); 
    $stmt->bind_param("ii", $employee_id, $manager_id); 
    $stmt->execute(); 
    $result = $stmt->get_result(); 

    if ($result->num_rows > 0) { 
        $employee = $result->fetch_assoc(); 
    } else { 
        echo "Employee not found or you don't have permission."; 
        exit(); 
    } 
} else { 
    echo "Invalid employee ID."; 
    exit(); 
} 

if ($_SERVER["REQUEST_METHOD"] == "POST") { 
    $name = $_POST['name']; 
    $email = $_POST['email']; 
    $phone = $_POST['phone']; 

    $picture = $employee['picture']; 

    if (isset($_FILES['picture']) && $_FILES['picture']['error'] == UPLOAD_ERR_OK) {
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
        $extension = strtolower(pathinfo($_FILES['picture']['name'], PATHINFO_EXTENSION));

        if (!in_array($extension, $allowed_extensions)) {
            echo "Invalid file type.";
            exit();
        }

        $target_dir = "uploads/"; 
        $target_file = $target_dir . basename($_FILES['picture']['name']);

        if (move_uploaded_file($_FILES['picture']['tmp_name'], $target_file)) {
            $picture = $target_file; 
        } else {
            echo "Error uploading file.";
            exit();
        }
    }

    $stmt = $conn->prepare("UPDATE employees SET name = ?, email = ?, phone = ?, picture = ? WHERE id = ? AND manager_id = ?"); 
    if ($stmt) { 
        $stmt->bind_param("ssssii", $name, $email, $phone, $picture, $employee_id, $manager_id); 
        $stmt->execute(); 
        $stmt->close(); 

        header("Location: index.php"); 
        exit(); 
    } else { 
        echo "Error preparing statement: " . $conn->error; 
    } 
} 
?> 

<div class="container"> 
    <h2>Edit Employee</h2> 
    <form method="post" action="" enctype="multipart/form-data"> 
        <div class="form-group"> 
            <label for="name">Name:</label> 
            <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($employee['name']); ?>" required> 
        </div> 
        <div class="form-group"> 
            <label for="email">Email:</label> 
            <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($employee['email']); ?>" required> 
        </div> 
        <div class="form-group"> 
            <label for="phone">Phone:</label> 
            <input type="text" class="form-control" id="phone" name="phone" value="<?php echo htmlspecialchars($employee['phone']); ?>" required> 
        </div> 
        <div class="form-group"> 
            <label for="picture">Upload New Picture (optional):</label> 
            <input type="file" class="form-control" id="picture" name="picture" accept="image/*"> 
            <img src="<?php echo htmlspecialchars($employee['picture']); ?>" alt="Current Picture" width="100" class="mt-2">
        </div> 
        <button type="submit" class="btn btn-primary">Update Employee</button> 
        <a href="index.php" class="btn btn-secondary">Cancel</a> 
    </form> 
</div>