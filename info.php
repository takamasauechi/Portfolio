<?php
session_start();

$message = ""; // メッセージ用の変数を初期化
$form_visible = true; // フォームの表示状態を初期化

// ログインしている場合はセッションからユーザー情報を取得
if (isset($_SESSION['username']) && isset($_SESSION['email'])) {
    $name = $_SESSION['username'];
    $email = $_SESSION['email'];
} else {
    // ログインしていない場合は初期値を空に設定
    $name = "";
    $email = "";
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // フォームから送信されたデータを取得
    $name = $_POST['name'];
    $email = $_POST['email'];
    $title = $_POST['title'];
    $message = $_POST['message'];

    $host = 'localhost';
    $dbname = 'cinema';
    $username = 'root';
    $password = '';

    $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    try {
        $pdo = new PDO($dsn, $username, $password, $options);

        // お問い合わせ情報をデータベースに挿入
        $stmt = $pdo->prepare("INSERT INTO info (Name, mail, contactTitle, message) VALUES (:name, :email, :title, :message)");
        $stmt->execute(['name' => $name, 'email' => $email, 'title' => $title, 'message' => $message]);

        // お問い合わせが成功したことをユーザーに通知
        $message = "お問い合わせありがとうございました。"; // メッセージをセット
        $form_visible = false; // フォームを非表示にする
    } catch (PDOException $e) {
        // エラーが発生した場合はエラーメッセージを表示して処理を終了
        echo "データベースエラー: " . $e->getMessage();
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="reset.css">
    <link rel="stylesheet" href="info.css">
    <title>お問い合わせフォーム</title>
</head>

<body>
    <h1>お問い合わせ</h1>
    <?php if (!empty($message)) : ?>
        <p><?= $message ?></p>
        <script>
            setTimeout(function() {
                window.location.href = 'index.php';
            }, 1500); // 1.5秒後にホームにリダイレクト
        </script>
    <?php endif; ?>
    <?php if ($form_visible) : ?>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div>
                <label for="name">名前：</label>
                <!-- ユーザー名の初期値としてセッションから取得した値を表示 -->
                <input type="text" id="name" name="name" value="<?= htmlspecialchars($name); ?>" required>
            </div>
            <div>
                <label for="email">メールアドレス：</label>
                <!-- メールアドレスの初期値としてセッションから取得した値を表示 -->
                <input type="email" id="email" name="email" value="<?= htmlspecialchars($email); ?>" required>
            </div>
            <div>
                <label for="title">タイトル：</label>
                <input type="text" id="title" name="title" required>
            </div>
            <div>
                <label for="message">メッセージ：</label>
                <textarea id="message" name="message" required></textarea>
            </div>
            <div>
                <button type="submit">送信</button>
            </div>
        </form>
    <?php endif; ?>
</body>

</html>