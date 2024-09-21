<?php  
session_start();  
include 'connect.php';  
include 'header.php';  

if (!isset($_SESSION['manager_id'])) {  
    header("Location: login.php");  
    exit();  
}  

$manager_id = $_SESSION['manager_id'];  

$stmt = $conn->prepare("SELECT name FROM managers WHERE id = ?"); 
$stmt->bind_param("i", $manager_id); 
$stmt->execute(); 
$result = $stmt->get_result(); 
$manager = $result->fetch_assoc(); 
$manager_name = $manager['name']; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {  
    if (isset($_POST['add'])) {  
        $name = $_POST['name'];  
        $email = $_POST['email'];  
        $phone = $_POST['phone'];  

        $picture = '';
        if (isset($_FILES['picture']) && $_FILES['picture']['error'] == UPLOAD_ERR_OK) {
            $target_dir = "uploads/"; 
            $target_file = $target_dir . basename($_FILES['picture']['name']);

            
            if (move_uploaded_file($_FILES['picture']['tmp_name'], $target_file)) {
                $picture = $target_file; 
            } else {
                echo "Error uploading file.";
                exit();
            }
        }

        $stmt = $conn->prepare("INSERT INTO employees (name, email, phone, picture, manager_id) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssi", $name, $email, $phone, $picture, $manager_id);

        if ($stmt->execute()) {
            header("Location: index.php");
            exit();
        } else {
            echo "Error: " . $stmt->error;
        }

        $stmt->close();
    }

    if (isset($_POST['delete'])) {  
        $employee_id = $_POST['employee_id'];  
        $stmt = $conn->prepare("DELETE FROM employees WHERE id = ? AND manager_id = ?");  
        if ($stmt) {  
            $stmt->bind_param("ii", $employee_id, $manager_id);  
            $stmt->execute();  
            $stmt->close();  
        } else {  
            echo "Error preparing statement: " . $conn->error;  
        }  
    }  
}  

$result = $conn->query("SELECT * FROM employees WHERE manager_id = $manager_id");  
if (!$result) {  
    echo "Error executing query: " . $conn->error;  
}  
?>  

<div class="container">      
    <h2>Welcome, <?php echo htmlspecialchars($manager_name); ?></h2>  
    <div class="text-right mb-3">  
        <a href="logout.php" class="btn btn-danger">Logout</a>  
    </div> 
    
    <form method="post" action="" enctype="multipart/form-data">  
        <h3>Add Employee</h3>  
        <div class="form-group">  
            <label for="name">Name:</label>  
            <input type="text" class="form-control" id="name" name="name" required>  
        </div>  
        <div class="form-group">  
            <label for="email">Email:</label>  
            <input type="email" class="form-control" id="email" name="email" required>  
        </div>  
        <div class="form-group">  
            <label for="phone">Phone:</label>  
            <input type="text" class="form-control" id="phone" name="phone" required>  
        </div>  
        <div class="form-group">  
            <label for="picture">Upload Picture:</label>  
            <input type="file" class="form-control" id="picture" name="picture" accept="image/*" required>  
        </div>  
        <button type="submit" name="add" class="btn btn-primary">Add Employee</button>  
    </form>  

    <h3>Employees</h3>  
    <table class="table">  
        <thead>  
            <tr>  
                <th>Name</th>  
                <th>Email</th>  
                <th>Phone</th>  
                <th>Picture</th>  
                <th>Actions</th>  
            </tr>  
        </thead>  
        <tbody>  
            <?php if ($result->num_rows > 0): ?><?php while ($row = $result->fetch_assoc()): ?>  
                    <tr>  
                        <td><?php echo htmlspecialchars($row['name']); ?></td>  
                        <td><?php echo htmlspecialchars($row['email']); ?></td>  
                        <td><?php echo htmlspecialchars($row['phone']); ?></td>  
                        <td><img src="<?php echo htmlspecialchars($row['picture']); ?>" alt="Picture" width="50"></td>  
                        <td>  
                            <form method="post" action="" style="display:inline;"> 
                                <input type="hidden" name="employee_id" value="<?php echo htmlspecialchars($row['id']); ?>">  
                                <button type="submit" name="delete" class="btn btn-danger mr-2">Delete</button>  
                            </form> 
                            <a href="edit_employee.php?id=<?php echo $row['id']; ?>" class="btn btn-warning">Edit</a> 
                        </td>  
                    </tr>  
                <?php endwhile; ?>  
            <?php else: ?>  
                <tr>  
                    <td colspan="5">No employees found</td>  
                </tr>  
            <?php endif; ?>  
        </tbody>  
    </table>  
</div>