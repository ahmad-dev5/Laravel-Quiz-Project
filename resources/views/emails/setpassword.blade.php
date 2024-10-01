<!-- 
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Set Your Password</title>
</head>
<body>
    <h1>Hello, {{ $user->name }}!</h1>
    <p>
        @if($user->hasRole('manager'))
            You have been added as a manager. Please click the link below to set up your password:
        @else
            You have been accepted as a student. Please click the link below to set up your password:
        @endif
    </p>
    
    <a href="{{ url('/set-password?token=' . $token . '&email=' . urlencode($user->email)) }}">Set Your Password</a>

    <p>This link will expire in 24 hours.</p>
</body>
</html> -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Set Your Password</title>
</head>
<body>
    <h1>Hello, {{ $user->name }}!</h1>
    <p>
        @if($user->hasRole('manager'))
            You have been added as a Manager. Please click the link below to set up your password:
        @elseif($user->hasRole('supervisor'))
            You have been added as a Supervisor. Please click the link below to set up your password:
        @else
            You have been accepted as a Student. Please click the link below to set up your password:
        @endif
    </p>
    
    <a href="{{ url('/set-password?token=' . $token . '&email=' . urlencode($user->email)) }}">Set Your Password</a>

    <p>This link will expire in 24 hours.</p>
</body>
</html>
