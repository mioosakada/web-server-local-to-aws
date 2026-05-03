<?php
$host = 'localhost';
$db   = 'portfolio_db';
$user = 'app_user';
$pass = 'Password123!';

// DB接続
$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die('DB接続失敗');
}

// 投稿処理
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $content = $_POST['content'];

    $sql = "INSERT INTO posts (user_id, title, content)
            VALUES (1, '$title', '$content')";
    $conn->query($sql);
}
?>

<h2>投稿</h2>
<form method="POST">
  <input type="text" name="title" placeholder="タイトル"><br>
  <textarea name="content" placeholder="内容"></textarea><br>
  <button type="submit">送信</button>
</form>

<h2>一覧</h2>

<?php
$sql = "SELECT users.name, posts.title, posts.content
        FROM posts
        JOIN users ON users.id = posts.user_id";

$result = $conn->query($sql);

while ($row = $result->fetch_assoc()) {
    echo "<p>{$row['name']} : {$row['title']} - {$row['content']}</p>";
}
?>