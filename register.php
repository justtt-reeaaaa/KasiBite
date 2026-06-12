<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register As</title>
    <style>
        body {
            font-family: "Segoe UI", Poppins, sans-serif;
            background: #f4f4f4;
            margin: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .card {
            background: #f3e9de;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            text-align: center;
            width: 350px;
        }
        h2 {
            color: #444;
            margin-bottom: 25px;
        }
        a {
            display: block;
            background: #e89631;
            color: #f6f1f1;
            text-decoration: none;
            padding: 12px;
            margin: 10px 0;
            border-radius: 6px;
            font-weight: bold;
            transition: background 0.3s ease;
        }
        a:hover {
            background: #46657a;
        }
    </style>
</head>
<body>
    <div class="card">
        <h2>Register as</h2>

        <a href="register_form.php?role=buyer">Buyer</a>
        <a href="register_form.php?role=seller">Seller</a>
    </div>
</body>
</html>
