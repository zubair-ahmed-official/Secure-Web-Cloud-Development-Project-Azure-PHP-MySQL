<?php

class UserModel {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function updateDiscordAccessToken($username, $accessToken) {
        $stmt = $this->db->prepare("UPDATE users SET discord_access_token = ? WHERE username = ?");
        $stmt->bind_param("ss", $accessToken, $username);

        return $stmt->execute();
    }
}
?>
