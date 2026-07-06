<?php

header("Content-Type: application/json");

require_once "../config/firebase.php";

try {

    $documents = $db->collection("users")->documents();

    $users = [];

    foreach ($documents as $document) {

        if (!$document->exists()) {
            continue;
        }

        $data = $document->data();

        // Konversi Timestamp Firestore menjadi string
        $createdAt = "";
        if (isset($data["createdAt"])) {
            try {
                $createdAt = $data["createdAt"]
                    ->get()
                    ->format("Y-m-d H:i:s");
            } catch (Exception $e) {
                $createdAt = "";
            }
        }

        $lastLogin = "";
        if (isset($data["lastLogin"])) {
            try {
                $lastLogin = $data["lastLogin"]
                    ->get()
                    ->format("Y-m-d H:i:s");
            } catch (Exception $e) {
                $lastLogin = "";
            }
        }

        $users[] = [

            "uid" => $document->id(),

            "displayName" => $data["displayName"] ?? "",

            "email" => $data["email"] ?? "",

            "photoURL" => $data["photoURL"] ?? "",

            "isOnline" => $data["isOnline"] ?? false,

            "createdAt" => $createdAt,

            "lastLogin" => $lastLogin,

            "currentManga" => $data["currentManga"] ?? "",

            "currentChapter" => $data["currentChapter"] ?? ""

        ];

    }

    // Online paling atas
    usort($users, function ($a, $b) {

        return ($b["isOnline"] <=> $a["isOnline"]);

    });

    echo json_encode([
        "success" => true,
        "total" => count($users),
        "data" => $users
    ]);

} catch (Exception $e) {

    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);

}