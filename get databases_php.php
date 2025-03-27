<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "hotel_management";

// Create connection with error handling
try {
    $conn = new mysqli($servername, $username, $password, $dbname);
    
    // Check connection
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    
    // SQL query to fetch table and column details
    $sql = "SELECT TABLE_NAME, COLUMN_NAME, DATA_TYPE, IS_NULLABLE, COLUMN_TYPE 
            FROM INFORMATION_SCHEMA.COLUMNS 
            WHERE TABLE_SCHEMA = '$dbname'";

    $result = $conn->query($sql);

    // Check if any results are returned
    if ($result->num_rows > 0) {
        echo "<table border='1'>
                <tr>
                    <th>Table Name</th>
                    <th>Column Name</th>
                    <th>Data Type</th>
                    <th>Is Nullable</th>
                    <th>Column Type</th>
                </tr>";
        while ($row = $result->fetch_assoc()) {
            echo "<tr>
                    <td>{$row['TABLE_NAME']}</td>
                    <td>{$row['COLUMN_NAME']}</td>
                    <td>{$row['DATA_TYPE']}</td>
                    <td>{$row['IS_NULLABLE']}</td>
                    <td>{$row['COLUMN_TYPE']}</td>
                  </tr>";
        }
        echo "</table>";
    } else {
        echo "No tables found.";
    }
    
} catch (Exception $e) {
    // Handle any errors that occur during the connection or query execution
    echo "Error: " . $e->getMessage();
} finally {
    // Close the connection
    if (isset($conn) && $conn->ping()) {
        $conn->close();
    }
}
?>
