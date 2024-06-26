<?php
session_start();

// カートに商品を追加する処理
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["add-to-cart"])) {
    // 商品データを取得し、セッションに保存する
    $item = [
        "id" => $_POST["id"],
        "title" => $_POST["title"],
        "imageUrl" => $_POST["imageUrl"],
        "price" => $_POST["price"],
        "quantity" => 1 // 初期数量を1とする
    ];

    // セッションに商品を追加
    $_SESSION["cart"][] = $item;
}
?>

<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8" />
    <title>映画管理システム</title>
    <link rel="stylesheet" href="reset.css" />
    <link rel="stylesheet" href="style.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" />
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
</head>

<body>
    <div id="header">

        <div id="logo">
            <h1>takamasa<br />DVD店</h1>
            <h1>uechi<br />DVD店</h1>
        </div>

        <?php if (isset($_SESSION['username'])) : ?>
            <div id="right">
                めんそーれ！！
                <div id="greeting">
                    <span id="user-greeting"><?= $_SESSION['username'] ?></span>様
                </div>
                <a class="big" href="mypage.php" id="myp">mypage</a>

                <a class="smol" href="logout.php" id="logout-link">退出</a>
            </div>
        <?php else : ?>

            <div class="regi">
                <!-- 会員登録 -->
                <a class="r" id="register-link" href="register.html">会員登録</a>
                <!-- loginForm -->
                <form id="login-form" action="login.php" method="POST">
                    <input type="text" id="username" placeholder="ユーザーネーム" name="username" autocomplete="username" required />
                    <input type="password" id="password" placeholder="パスワード" name="password" autocomplete="current-password" required />
                    <div toggle="#password" class="fa fa-fw fa-eye field-icon toggle-password"></div>
                    <!-- 登録 -->
                    <button class="bu" type="submit">ログイン</button>
                </form>
            </div>
        <?php endif; ?>

        <a id="info-link" href="info.php"><img src="./otoi.png" alt="お問い合わせ"></a>
    </div>

    <div id="genre-buttons">
        <button class="genre reverse" data-genre="comedy">comdy</button>
        <button class="genre reverse" data-genre="action">action</button>
        <button class="genre reverse" data-genre="fantasy">fantasy</button>
        <button class="genre reverse" data-genre="romance">romance</button>
        <button class="genre reverse" data-genre="horror">horror</button>
        <button class="genre reverse" data-genre="mystery">mystery</button>
        <button class="genre reverse" data-genre="sports">sports</button>
        <button class="genre reverse" data-genre="war">war</button>
        <!-- 他のジャンルのボタンも同様に追加 -->

        <div class="search-box">
            <!-- 映画を検索 -->
            <input type="text" id="search-input" placeholder="映画検索" />
            <button id="search-button">検索</button>
        </div>
    </div>

    <div id="movies-display">
        <div id="mini-cart">
            <!-- カート -->
            <h2>カート</h2>
            <ul id="mini-cart-items">
                <!-- カート内の商品がここに表示されます -->
            </ul>
            <div id="mini-cart-total">
                <!-- 金額　　円 -->
                価格: <span id="mini-cart-total-price">0</span>円
            </div>
            <!-- ご注文お手続きへ -->
            <a href="cart.php" id="mini-cart-link">ご注文お手続きへ</a>
        </div>
    </div>

    <script>
        $(document).ready(function() {


            // 目のアイコンをクリックしたときの動作を定義する
            $(".toggle-password").click(function() {
                // 対応するパスワード入力フィールドを取得
                var input = $($(this).attr("toggle"));
                // パスワードの入力タイプを切り替える（テキスト ↔ パスワード）
                if (input.attr("type") === "password") {
                    input.attr("type", "text");
                } else {
                    input.attr("type", "password");
                }
            });

            $("#login-form").submit(function(event) {
                // フォームのデフォルトの送信を防止
                event.preventDefault();

                // フォームデータを取得
                var formData = $(this).serialize();

                // AJAXを使用してサーバーにフォームデータを送信
                $.ajax({
                    url: "login.php",
                    type: "POST",
                    data: formData,
                    dataType: "json",
                    success: function(response) {
                        // ログインに成功した場合の処理

                        if (response.status === "success") {
                            $("#regi").hide(); // ログインフォームを非表示にする
                            $("#right").show(); // 挨拶を表示する
                            $("#user-greeting").text(response.username);
                            location.reload(); // ページをリロードする
                        } else {
                            // ログインに失敗しました。ユーザーネームまたはパスワードが間違っています。
                            alert("ログインに失敗しました。ユーザーネームまたはパスワードが間違っています。");
                        }
                    },
                    error: function() {
                        // エラーメッセージを表示
                        alert("ログインに失敗しました。ユーザーネームまたはパスワードが間違っています。");
                    },
                });
            });

            // ログアウトリンクがクリックされた場合の処理
            $("#logout-link").click(function(event) {
                event.preventDefault(); // デフォルトのリンクの動作を防止

                // ログアウトの非同期リクエストを送信
                $.ajax({
                    url: "logout.php",
                    type: "POST",
                    dataType: "json",
                    success: function(response) {
                        // ログアウト成功後、ページを自動的に戻る
                        window.location.href = "index.php"; // ログアウト後にトップページにリダイレクト
                    },
                    error: function() {
                        // ログアウトに失敗しました
                        alert("ログアウトに失敗しました。");
                    },
                });
            });


            var genreFlags = {};

            $(".genre").click(function() {
                var genre = $(this).data("genre");

                // ジャンルごとのフラグを初期化する
                if (!genreFlags.hasOwnProperty(genre)) {
                    genreFlags[genre] = {
                        loaded: false,
                        visible: false
                    };
                }

                // データが既に取得済みであれば、表示・非表示を切り替える
                if (genreFlags[genre].loaded) {
                    toggleMovies(genre);
                } else {
                    $.ajax({
                        url: "get_movies.php",
                        type: "GET",
                        data: {
                            genre: genre
                        },
                        dataType: "json",
                        success: function(movies) {
                            addMovies(movies, genre);
                            genreFlags[genre].loaded = true;
                            toggleMovies(genre);
                        },
                        error: function() {
                            // 映画の取得に失敗しました。
                            alert(" 映画の取得に失敗しました。");
                        }
                    });
                }
            });

            function addMovies(movies, genre) {
                $.each(movies, function(i, movie) {
                    // ファイル名の部分を抽出
                    var fileName = movie.image_path.replace(/"/g, "").split("/").pop();

                    // 画像の相対パスを生成
                    var imageUrl = "movies_cinema/" + fileName;

                    // imageUrlを使用して<img>タグを生成
                    var movieHtml =
                        '<div class="movie" data-genre="' + genre + '">' +
                        '<img src="' +
                        imageUrl +
                        '" alt="' +
                        movie.title +
                        '">' +
                        "<h3>" +
                        movie.title +
                        "</h3>" +
                        "<p>価格: ￥" +
                        movie.amount +
                        "</p>" +
                        "<p>ジャンル: " +
                        movie.genre +
                        "</p>" +
                        '<p>在庫: <span class="stock" data-stock="' +
                        movie.stock +
                        '">' +
                        movie.stock +
                        "</span></p>" +
                        '<button class="decrease">-</button>' +
                        '<input type="text" class="quantity" value="0">' +
                        '<button class="increase">+</button>' +
                        '<button class="add-to-cart" data-id="' +
                        movie.id +
                        '" data-price="' +
                        movie.amount +
                        '">カートに追加</button>' +
                        "</div>";

                    $("#movies-display").append(movieHtml);
                });
            }

            function toggleMovies(genre) {
                var selector = ".movie[data-genre='" + genre + "']";
                if (genreFlags[genre].visible) {
                    $(selector).hide();
                    genreFlags[genre].visible = false;
                } else {
                    $(selector).show();
                    genreFlags[genre].visible = true;
                }
            }

            // 数量増減ボタンのイベント
            $(document).on("click", ".increase", function() {
                var input = $(this).prev(".quantity");
                var currentValue = parseInt(input.val());
                var stock = parseInt(
                    $(this)
                    .parent()
                    .find(".stock")
                    .data("stock")
                );
                if (currentValue < stock) {
                    input.val(currentValue + 1);
                } else {
                    // 在庫数を超える場合にユーザーに警告する「在庫が不足しています。在庫数」
                    alert("在庫が不足しています: " + stock);
                }
            });
            $(document).on("click", ".decrease", function() {
                var input = $(this).next(".quantity");
                var currentValue = parseInt(input.val());
                if (currentValue > 0) {
                    input.val(currentValue - 1);
                } else {
                    // 0未満にはできないように警告する「数量は0未満にはできません。」
                    alert("数量は0未満にはできません。");
                }
            });

            // カートに追加ボタンのイベント
            $(document).on("click", ".add-to-cart", function() {
                var id = $(this).data("id");
                var quantity = $(this)
                    .parent()
                    .find(".quantity")
                    .val();
                var price = $(this).data("price");
                var imageUrl = $(this).parent().find("img").attr("src"); // 商品画像のURLを取得
                var title = $(this).parent().find("h3").text(); // 商品タイトルを取得

                // 必要なデータが正しく取得されているかを確認
                console.log("ID: " + id);
                console.log("Quantity: " + quantity);
                console.log("Price: " + price);
                console.log("Image URL: " + imageUrl);
                console.log("Title: " + title);

                if (parseInt(quantity) > 0) {
                    $.ajax({
                        url: "add_to_cart.php",
                        type: "POST",
                        data: {
                            id: id,
                            quantity: quantity,
                            price: price,
                            imageUrl: imageUrl, // 商品画像のURLを追加
                            title: title
                        },
                        success: function(response) {
                            // 成功したときの処理
                            $("#mini-cart").show(); // ミニカートを表示する
                            updateMiniCartLink();
                            // 成功後に数量を0にリセット
                            $(".quantity").filter(function() {
                                return $(this).parent().find(".add-to-cart").data("id") === id;
                            }).val(0);
                        },
                        error: function() {
                            alert("カートに追加できませんでした。"); //カートに追加できませんでした。
                        },
                    });
                } else {
                    alert("数量を選択してください"); //数量を選択してください
                }
            });

            function updateCartTotal() {
                $.ajax({
                    url: "get_cart_total.php",
                    type: "GET",
                    dataType: "text", // まずレスポンスをテキストとして取得
                    success: function(response) {
                        console.log(
                            "Response as text:",
                            response
                        ); // レスポンスをテキストとしてログ出力
                        var cleanedResponse = response.replace(
                            /<!--.*?-->/gs,
                            ""
                        ); // HTMLコメントを削除
                        try {
                            var jsonData =
                                JSON.parse(cleanedResponse); // コメントを取り除いたレスポンスをJSONとしてパース
                            var total = jsonData.total;
                            $("#mini-cart-total-price").text(
                                total
                            ); // ミニカートの特定の要素に合計金額を表示
                        } catch (error) {
                            console.error(
                                "JSON parsing error:",
                                error
                            ); // パースエラーをログ出力
                            console.error(
                                "Cleaned response:",
                                cleanedResponse
                            ); // エラー発生時のクリーニング済みレスポンスをログ出力
                        }
                    },
                    error: function(
                        jqXHR,
                        textStatus,
                        errorThrown
                    ) {
                        console.error(
                            "AJAX error:",
                            textStatus,
                            errorThrown
                        ); // AJAXリクエスト自体のエラーをログ出力
                        alert("合計金額の取得に失敗しました。");
                    },
                });
            }

            function updateMiniCartLink() {

                updateCartTotal();
            }

            // 検索ボタンがクリックされたときの処理
            let displayedItems = []; // 表示されている商品のIDを記録する配列

            // 検索ボタンがクリックされたときの処理
            $("#search-button").click(function() {
                var keyword = $("#search-input").val().trim();
                if (keyword.length >= 1) {
                    searchMovies(keyword);
                }
            });

            // 商品を表示する関数
            function displayMovie(movie) {
                if (!displayedItems.includes(movie.id)) {
                    // ファイル名の部分を抽出
                    var fileName = movie.image_path.replace(/"/g, "").split("/").pop();
                    // 画像の相対パスを生成
                    var imageUrl = "movies_cinema/" + fileName;
                    // imageUrlを使用して<img>タグを生成
                    var movieHtml =
                        '<div class="movie">' +
                        '<img src="' +
                        imageUrl +
                        '" alt="' +
                        movie.title +
                        '">' +
                        "<h3>" +
                        movie.title +
                        "</h3>" +
                        "<p>価格: " + movie.amount + "</p>" +
                        "<p>ジャンル: " + movie.genre + "</p>" +
                        '<p>在庫: <span class="stock" data-stock="' + movie.stock + '">' + movie.stock + "</span></p>" +
                        '<button class="decrease">-</button>' +
                        '<input type="text" class="quantity" value="0">' +
                        '<button class="increase">+</button>' +
                        '<button class="add-to-cart" data-id="' + movie.id + '" data-price="' + movie.amount + '">カートに追加</button>' +
                        "</div>";

                    // 新しい商品を表示する
                    $("#movies-display").append(movieHtml);

                    // 表示された商品のIDを記録
                    displayedItems.push(movie.id);
                }
            }

            // キーワード検索を実行する関数
            function searchMovies(keyword) {
                $.ajax({
                    url: "search_movies.php",
                    type: "GET",
                    data: {
                        keyword: keyword
                    },
                    dataType: "json",
                    success: function(movies) {
                        // 商品表示領域をクリアしない

                        $.each(movies, function(i, movie) {
                            displayMovie(movie);
                        });
                    },
                    error: function() {
                        alert("商品の検索に失敗しました。");
                    }
                });
            }




        });
    </script>
</body>

</html>