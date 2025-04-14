// ...existing code...
public function login($username, $password) {
    $query = "SELECT * FROM users WHERE name = :username LIMIT 1";
    $stmt = $this->db->prepare($query);
    $stmt->bindParam(':username', $username);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if (password_verify($password, $user['password'])) { // Verify the hashed password
            // ...existing code for successful login...
            return true;
        }
    }
    return false; // Incorrect username or password
}
// ...existing code...
