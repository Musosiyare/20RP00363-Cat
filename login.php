<?php
// Include GitHub API config file
require_once 'gitConfig.php';

// Include and initialize user class
require_once 'User.class.php';
$user = new User();

if(isset($accessToken)){
    // Get the user profile info from Github
    $gitUser = $gitClient->apiRequest($accessToken);
    
    if(!empty($gitUser)){
        // User profile data
        $gitUserData = array();
        $gitUserData['oauth_provider'] = 'github';
        $gitUserData['oauth_uid'] = !empty($gitUser->id)?$gitUser->id:'';
        $gitUserData['name'] = !empty($gitUser->name)?$gitUser->name:'';
        $gitUserData['username'] = !empty($gitUser->login)?$gitUser->login:'';
        $gitUserData['email'] = !empty($gitUser->email)?$gitUser->email:'';
        $gitUserData['location'] = !empty($gitUser->location)?$gitUser->location:'';
        $gitUserData['picture'] = !empty($gitUser->avatar_url)?$gitUser->avatar_url:'';
        $gitUserData['link'] = !empty($gitUser->html_url)?$gitUser->html_url:'';
        
        // Insert or update user data to the database
        $userData = $user->checkUser($gitUserData);
        
        // Put user data into the session
        $_SESSION['userData'] = $userData;
        
        // Render Github profile data
        $output  = '<h2>Github Profile Details</h2>';
        $output .= '<img src="'.$userData['picture'].'" />';
        $output .= '<p>ID: '.$userData['oauth_uid'].'</p>';
        $output .= '<p>Name: '.$userData['name'].'</p>';
        $output .= '<p>Login Username: '.$userData['username'].'</p>';
        $output .= '<p>Email: '.$userData['email'].'</p>';
        $output .= '<p>Location: '.$userData['location'].'</p>';
        $output .= '<p>Profile Link :  <a href="'.$userData['link'].'" target="_blank">Click to visit GitHub page</a></p>';
        $output .= '<p>Logout from <a href="logout.php">GitHub</a></p>'; 
    }else{
        $output = '<h3 style="color:red">Some problem occurred, please try again.</h3>';
    }
    
}elseif(isset($_GET['code'])){
    // Verify the state matches the stored state
    if(!$_GET['state'] || $_SESSION['state'] != $_GET['state']) {
        header("Location: ".$_SERVER['PHP_SELF']);
    }
    
    // Exchange the auth code for a token
    $accessToken = $gitClient->getAccessToken($_GET['state'], $_GET['code']);
  
    $_SESSION['access_token'] = $accessToken;
  
    header('Location: ./');
}else{
    // Generate a random hash and store in the session for security
    $_SESSION['state'] = hash('sha256', microtime(TRUE) . rand() . $_SERVER['REMOTE_ADDR']);
    
    // Remove access token from the session
    unset($_SESSION['access_token']);
  
    // Get the URL to authorize
    $loginURL = $gitClient->getAuthorizeURL($_SESSION['state']);
    
    // Render Github login button
    $output = '<a href="'.htmlspecialchars($loginURL).'"><img src="img/github-login.png"></a>';
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <script src='https://kit.fontawesome.com/a076d05399.js' crossorigin='anonymous'></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
<!-- Latest compiled JavaScript -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <title>Document</title>
</head>
<body>
    <form action="insert_user.php" method="post">
    <header class="text-white p-4 text-center"style="background-color:BLACK; color:white;">
<h5>IREMBO | Practical CAT</h5>
</header>
<!-- form container -->
<div class="d-flex justify-content-center align-items-center" style="height:80vh;">
    <!-- <h1>Test</h1> -->
        <div class="border px-4 pt-4 position-relative text-center shadow w-25" style="border-right:5px;">
           <h6><b>Connect to IREMBO</b> </h6><br><br><br>
           <!-- <div class="my-5">
                <button type="submit" name="facebook" class="form-control btn btn" style="background-color:black;color:white;font-size:18px; font-weight:bold;">
                    <i class="fa fa-github text-white" aria-hidden="true" style="font-size:25px;"></i>
                    Continue with GitHub
                </button><br><br><br><br><br>
                
           </div> -->
           <div class="container mt-5">
    <!-- Display login button / GitHub profile information -->
    <div class="wrapper mt-5 mb-5"><?php echo $output; ?></div>
</div>
        </div>
</div>
</form>
</body>
</html>