<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assignment 2 - Ahmed</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            background-color: #f4f6f8;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
        }
        .card {
            background: #ffffff;
            padding: 40px 60px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        h1 {
            color: #e74430;
            margin-bottom: 10px;
        }
        p {
            color: #333;
            font-size: 1.2rem;
        }
    </style>
</head>
<body>
    <div class="card">
        <h1>Assignment 2</h1>
        <p>My name is Ahmed Al Alawi and today's date is {{ date('d-m-Y') }}.</p>
    </div>
</body>
</html>
