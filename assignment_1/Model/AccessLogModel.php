<?php
class AccessLogModel {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function logPageAccess($ip_address, $url, $username, $access_allowed) {
        $sql = "INSERT INTO access_log (ip_address, requested_url, username, access_allowed) VALUES (?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("sssi", $ip_address, $url, $username, $access_allowed);
        $stmt->execute();
    }

    public function getLogs($ip_filter = null) {
        $sql = "SELECT * FROM access_log";
        if ($ip_filter) {
            $sql .= " WHERE ip_address = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param("s", $ip_filter);
        } else {
            $stmt = $this->db->prepare($sql);
        }

        $stmt->execute();
        $result = $stmt->get_result();

        $logs = [];
        while ($row = $result->fetch_assoc()) {
            $logs[] = $row;
        }

        return $logs;
    }

    public function getUserRole($username) {
        $sql = "SELECT role FROM users WHERE username = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }
}
?>
