<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/config.php'; // Corrected path to config.php

class VendorModel {
    public function getAllVendors() {
        global $connection;
        $stmt = $connection->query('SELECT * FROM vendors');
        return $stmt->fetchAll();
    }

    public function getVendorById($vendorId) {
        global $connection;
        $stmt = $connection->prepare('SELECT * FROM vendors WHERE id = :id');
        $stmt->execute(['id' => $vendorId]);
        return $stmt->fetch();
    }

    public function addVendor($vendorName, $vendorPhone, $vendorWebAddress, $vendorMailingAddress, $vendorEmailAddress) {
        global $connection;
        $stmt = $connection->prepare('INSERT INTO vendors (Vendor, phone, web_address, mailing_address, email_address) VALUES (:Vendor, :phone, :web_address, :mailing_address, :email_address)');
        return $stmt->execute([
            'Vendor' => $vendorName,
            'phone' => $vendorPhone,
            'web_address' => $vendorWebAddress,
            'mailing_address' => $vendorMailingAddress,
            'email_address' => $vendorEmailAddress
        ]);
    }

    public function deleteVendor($vendorId) {
        global $connection;
        $stmt = $connection->prepare('DELETE FROM vendors WHERE id = :id');
        return $stmt->execute(['id' => $vendorId]);
    }

    public function updateVendor($vendorId, $vendorName, $vendorPhone, $vendorWebAddress, $vendorMailingAddress, $vendorEmailAddress) {
        global $connection;
        $stmt = $connection->prepare('UPDATE vendors SET Vendor = :Vendor, phone = :phone, web_address = :web_address, mailing_address = :mailing_address, email_address = :email_address WHERE id = :id');
        return $stmt->execute([
            'id' => $vendorId,
            'Vendor' => $vendorName,
            'phone' => $vendorPhone,
            'web_address' => $vendorWebAddress,
            'mailing_address' => $vendorMailingAddress,
            'email_address' => $vendorEmailAddress
        ]);
    }
}
?>
