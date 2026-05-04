<?php
// Run this file once via browser to generate password hash
// Access: http://localhost/troywebapp/generate_hash.php

$password = 'EvilfNrfQ0W';
$hash = password_hash($password, PASSWORD_DEFAULT);

echo "<h2>Password Hash Generated</h2>";
echo "<p><strong>Password:</strong> $password</p>";
echo "<p><strong>Hash:</strong> <code>$hash</code></p>";
echo "<hr>";
echo "<h3>SQL Command to Insert Admin:</h3>";
echo "<pre>INSERT INTO users (full_name, email, password, role) VALUES 
('Admin', 'admin@swiftcivic.com', '$hash', 'admin');</pre>";

echo "<hr><p><strong>Delete this file after use!</strong></p>";
