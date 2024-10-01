<!DOCTYPE html>
<html>
<head>
    <title>Accepted!</title>
</head>
<body>
    <h1>Hi {{ $student->name }},</h1>
    <p>Congratulations! You have been accepted as a student. Please click the link below to set your password:</p>

    <a href="{{ $passwordSetupLink }}">Set Password</a>

    <p>This link is valid for 24 hours.</p>

    <p>Thank you,<br>Your Team</p>
</body>
</html>
