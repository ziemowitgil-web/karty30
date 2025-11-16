<!DOCTYPE html>
<html>
<head>
    <title>Nowy użytkownik</title>
</head>
<body>
<p>Witaj {{ $user->name }},</p>

<p>Zostałeś dodany do systemu TyfloKonsultacje .</p>

<p>Twoje dane logowania:</p>
<ul>
    <li>Email: {{ $user->email }}</li>
    <li>Hasło: {{ $password }}</li>
</ul>

<p>Zalecamy zalogowanie się i zmianę hasła po pierwszym logowaniu.</p>

<p>Pozdrawiamy,<br>Administrator</p>
</body>
</html>
