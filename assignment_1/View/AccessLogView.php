<?php
class AccessLogView {
    public static function displayHeader() {
        echo '<!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <link rel="stylesheet" href="css/access_log.css"> <!-- Link to the CSS file -->
            <title>Access Logs</title>
        </head>
        <body>';
    }

    public static function displayFooter() {
        echo '</body></html>';
    }

    public static function displayLogsTable($logs) {
        self::displayHeader(); // Include the header with CSS link
        echo "<h3>Logs in HTML Table</h3>";
        echo "<table class='logs-table'>";
        echo "<tr><th>Username</th><th>IP Address</th><th>Login Time</th><th>Access Allowed</th><th>Requested URL</th></tr>";
        foreach ($logs as $log) {
            echo "<tr>";
            echo "<td>" . (is_null($log['username']) ? 'NULL' : htmlspecialchars($log['username'])) . "</td>";
            echo "<td>" . htmlspecialchars($log['ip_address']) . "</td>";
            echo "<td>" . htmlspecialchars($log['login_time']) . "</td>";
            echo "<td>" . ($log['access_allowed'] ? 'Allowed' : 'Denied') . "</td>";
            echo "<td>" . htmlspecialchars($log['requested_url']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        self::displayFooter(); // Include the footer
    }

    public static function displayLogsList($logs) {
        self::displayHeader(); // Include the header with CSS link
        echo "<h3>Logs in Unordered List</h3>";
        echo "<ul class='logs-list'>";
        foreach ($logs as $log) {
            echo "<li>Username: " . htmlspecialchars($log['username']) . ", IP Address: " . htmlspecialchars($log['ip_address']) . ", Login Time: " . htmlspecialchars($log['login_time']) . ", " . ($log['access_allowed'] ? 'Allowed' : 'Denied') . ", " . htmlspecialchars($log['requested_url']) . "</li>";
        }
        echo "</ul>";
        self::displayFooter(); // Include the footer
    }

    public static function displayLogsJSON($logs) {
        self::displayHeader(); // Include the header with CSS link
        echo "<h3>Logs in JSON Format</h3>";
        echo "<pre class='logs-json'>";
        echo json_encode($logs, JSON_PRETTY_PRINT);
        echo "</pre>";
        self::displayFooter(); // Include the footer
    }

    public static function displayLogsXML($logs) {
        self::displayHeader(); // Include the header with CSS link
        echo "<h3>Logs in XML Format</h3>";
        $xml = new SimpleXMLElement('<logs/>');
        foreach ($logs as $log) {
            $log_entry = $xml->addChild('log');
            $log_entry->addChild('username', htmlspecialchars($log['username']));
            $log_entry->addChild('ip_address', htmlspecialchars($log['ip_address']));
            $log_entry->addChild('login_time', htmlspecialchars($log['login_time']));
            $log_entry->addChild('access_allowed', $log['access_allowed'] ? 'Allowed' : 'Denied');
            $log_entry->addChild('requested_url', htmlspecialchars($log['requested_url']));
        }
        echo "<pre class='logs-xml'>" . htmlentities($xml->asXML()) . "</pre>";
        self::displayFooter(); // Include the footer
    }
}
?>
