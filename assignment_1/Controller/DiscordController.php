<?php
require 'Model/UserModel.php';
use GuzzleHttp\Client;

class DiscordController {
    private $userModel;

    public function __construct($db) {
        $this->userModel = new UserModel($db);
    }

    public function handleCallback() {
        session_start();
        if (!isset($_SESSION['username'])) {
            header("Location: login_page.php");
            exit();
        }

        if (isset($_GET['code'])) {
            $accessToken = $this->getAccessToken($_GET['code']);
            if ($accessToken) {
                $_SESSION['access_token'] = $accessToken; 
                $username = $_SESSION['username'];

                if ($this->userModel->updateDiscordAccessToken($username, $accessToken)) {
                    echo "Access Token stored in database!";
                } else {
                    echo "Failed to store access token in database.";
                }

                header("Location: discord_profile.php");
                exit();
            } else {
                echo "Failed to get access token.";
            }
        } else {
            echo "Authorization code not received.";
        }
    }

    private function getAccessToken($code) {
        $clientId = "1287741099560931358"; 
        $clientSecret = "diT4gsrHKQmEoWyrQ2kKdhLYlBS9Ssi-"; 
        $redirectUri = "https://lab-d00a6b41-7f81-4587-a3ab-fa25e5f6d9cf.australiaeast.cloudapp.azure.com:7008/assignment_1/discord_callback.php"; 

        $client = new Client();
        
        try {
            $response = $client->post('https://discord.com/api/oauth2/token', [
                'form_params' => [
                    'client_id' => $clientId,
                    'client_secret' => $clientSecret,
                    'grant_type' => 'authorization_code',
                    'code' => $code,
                    'redirect_uri' => $redirectUri,
                ]
            ]);
            
            $data = json_decode($response->getBody(), true);
            return $data['access_token'];
        } catch (Exception $e) {
            error_log('Error: ' . $e->getMessage());
            return null;
        }
    }
}
?>
