<!DOCTYPE html>
<style>
/* here wuill be all css for email template */
</style>
<html>
<head>
    <title>ItsolutionStuff.com</title>
</head>
<body>
    <h1>{{ $details['title'] }}</h1><br>
    <h1>here is your one time otp  {{ $details['otp'] }}</h1>
    <p>{{ $details['body'] }}</p>
   
    <p>Thank you</p>
</body>
</html>