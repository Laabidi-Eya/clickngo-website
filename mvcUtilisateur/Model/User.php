<?php

require_once __DIR__ . '/../config.php'; // Ensure this path points to the file containing the Database class

class User
{
    private $id_user;
    private $fullname;
    private $full_name;
    private $email;
    private $password;
    private $date_inscription;
    private $role;
    private $num_user;
    private $facebook_id;
    private $google_id;
    private $profile_picture;
    private $ban_reason;
    private $is_verified; // Added this line 
    
    public static function getUserByPhone($db, $phone)
{
    $stmt = $db->prepare("SELECT * FROM user WHERE num_user = :phone LIMIT 1");
    $stmt->bindParam(':phone', $phone, PDO::PARAM_STR);
    $stmt->execute();
    $data = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($data) {
        $user = new self();
        $user->setIdUser($data['id_user']);
        $user->setFullName($data['full_name']);
        $user->setEmail($data['email']);
        $user->setPassword($data['password']);
        $user->setNumUser($data['num_user']);
        $user->setRole($data['role']);
        $user->setProfilePicture($data['profile_picture'] ?? null);
        $user->setIsVerified($data['is_verified'] ?? null);
        if (method_exists($user, 'setBanReason') && isset($data['ban_reason'])) {
            $user->setBanReason($data['ban_reason']);
        }
        if (method_exists($user, 'setFacebookId') && isset($data['facebook_id'])) {
            $user->setFacebookId($data['facebook_id']);
        }
        return $user;
    }
    return null;
}
    public static function getUserByEmail($db, $email)
    {
        $query = $db->prepare("SELECT * FROM user WHERE email = :email");
        $query->bindParam(':email', $email, PDO::PARAM_STR);
        $query->execute();
        return $query->fetch(PDO::FETCH_ASSOC);
    }

    public function getIsVerified()
    {
        return $this->is_verified;
    }

    public function setIsVerified($val)
    {
        $this->is_verified = $val;
    }


    // Removed duplicate getBanReason method to avoid redeclaration error.

    public function setBanReason($ban_reason)
    {
        $this->ban_reason = $ban_reason;
    }

    public function __construct(
        $id_user = null,
        $full_name = null,
        $email = null,
        $password = null,
        $date_inscription = null,
        $role = 'user',
        $num_user = null,
        $facebook_id = null,
        $google_id = null,
        $profile_picture = null,
        $ban_reason = null,
        $is_verified = 0
    ) {
        $this->id_user = $id_user;
        $this->full_name = $full_name;
        $this->email = $email;
        $this->password = $password;
        $this->date_inscription = $date_inscription;
        $this->role = $role;
        $this->num_user = $num_user;
        $this->facebook_id = $facebook_id;
        $this->google_id = $google_id;
        $this->profile_picture = $profile_picture;
        $this->ban_reason = $ban_reason;
        $this->is_verified = $is_verified; // ✅ Ajout important ici
    }


    public function addUser($fullName, $email, $password, $dateInscription, $role, $numUser, $facebookId, $googleId, $profilePicture)
    {
        $db = config::getConnexion();
        $sql = "INSERT INTO users (full_name, email, password, date_inscription, role, num_user, facebook_id, google_id, profile_picture)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $db->prepare($sql);
        $stmt->execute([$fullName, $email, $password, $dateInscription, $role, $numUser, $facebookId, $googleId, $profilePicture]);
    }


    public static function deleteUser($db, $id)
    {
        $stmt = $db->prepare("SELECT * FROM user WHERE id = ?");
        $stmt->execute([$id]);
        $user = $stmt->fetch();

        if ($user) {
            // L'utilisateur existe, procéder à la suppression
            $delete = User::deleteById($db, $id);
            if ($delete) {
                echo "Utilisateur supprimé avec succès.";
            } else {
                echo "Erreur lors de la suppression de l'utilisateur.";
            }
        } else {
            echo "Utilisateur introuvable.";
        }
    }

    public static function deleteById($db, $id)
    {
        $query = "DELETE FROM user WHERE id_user = :id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();  // Returns true if deletion was successful
    }



    // Getter pour la photo de profil
    public function getProfilePicture()
    {
        return $this->profile_picture;
    }

    public function setProfilePicture($profile_picture)
    {
        $this->profile_picture = $profile_picture;
    }


    // Getters and Setters
    public function getIdUser()
    {
        return $this->id_user;
    }

    public function setIdUser($idUser)
    {
        $this->id_user = $idUser;
    }

    public function getFullName()
    {
        return $this->full_name;
    }

    public function setFullName($fullName)
    {
        $this->full_name = $fullName;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function setEmail($email)
    {
        $this->email = $email;
    }

    public function getPassword()
    {
        return $this->password;
    }

    public function setPassword($password)
    {
        $this->password = $password;
    }

    public function getNumUser()
    {
        return $this->num_user;
    }

    public function setNumUser($numUser)
    {
        $this->num_user = $numUser;
    }

    // Removed duplicate getRole method to avoid redeclaration error.

    public function setRole($role)
    {
        $this->role = $role;
    }

    public function getFacebookId()
    {
        return $this->facebook_id;
    }

    public function setFacebookId($facebookId)
    {
        $this->facebook_id = $facebookId;
    }

    public function getGoogleId()
    {
        return $this->google_id;
    }

    public function setGoogleId($googleId)
    {
        $this->google_id = $googleId;
    }

    public static function findById($db, $id)
    {
        $stmt = $db->prepare("SELECT * FROM user WHERE id_user = ?");
        $stmt->execute([$id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            $user = new self();
            $user->setIdUser($result['id_user']);
            $user->setFullName($result['full_name']);
            $user->setEmail($result['email']);
            $user->setPassword($result['password']);
            $user->setRole($result['role']);
            $user->setNumUser($result['num_user']);
            $user->setProfilePicture($result['profile_picture']);
            $user->setBanReason($result['ban_reason']);
            return $user;
        }

        return null;
    }

    public function updateUser($db, $id_user)
    {
        $stmt = $db->prepare("UPDATE user SET role = :role, ban_reason = :ban_reason WHERE id_user = :id_user");
        $stmt->bindValue(':role', $this->getRole());
        $stmt->bindValue(':ban_reason', $this->getBanReason());
        $stmt->bindValue(':id_user', $id_user); // ✅ maintenant ça correspond parfaitement au nom dans la requête

        return $stmt->execute();
    }


    public function getRole()
    {
        return $this->role;
    }

    public function getBanReason()
    {
        return $this->ban_reason;
    }






    public static function getUserById($db, $id_user)
    {
        $stmt = $db->prepare("SELECT * FROM user WHERE id_user = ?");
        $stmt->execute([$id_user]);
        $userData = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($userData) {
            $user = new self();
            $user->setIdUser($userData['id_user']);
            $user->setFullName($userData['full_name']);
            $user->setEmail($userData['email']);
            $user->setPassword($userData['password']);
            $user->setRole($userData['role']);
            $user->setNumUser($userData['num_user']);
            $user->setProfilePicture($userData['profile_picture']);
            return $user;
        }

        return null;
    }

    public static function updatePasswordByEmail($db, $email, $hashedPassword)
    {
        $stmt = $db->prepare("UPDATE user SET password = :password WHERE email = :email");
        return $stmt->execute(['password' => $hashedPassword, 'email' => $email]);
    }


    public static function getPasswordHashById($db, $id_user)
    {
        $stmt = $db->prepare("SELECT password FROM user WHERE id_user = :id_user");
        $stmt->execute(['id_user' => $id_user]);
        return $stmt->fetchColumn();
    }



    public function save($db)
    {
        if ($this->id_user) {
            // Mise à jour
            $stmt = $db->prepare("UPDATE user SET 
                full_name = ?,
                email = ?,
                password = ?,
                num_user = ?,
                role = ?,
                facebook_id = ?,
                google_id = ?,
                profile_picture = ?
                WHERE id_user = ?");

            return $stmt->execute([
                $this->full_name,
                $this->email,
                $this->password,
                $this->num_user,
                $this->role,
                $this->facebook_id,
                $this->google_id,
                $this->profile_picture,
                $this->id_user
            ]);
        } else {
            // Insertion
            $stmt = $db->prepare("INSERT INTO user 
                (full_name, email, password, date_inscription, role, num_user, facebook_id, google_id, profile_picture) 
                VALUES (?, ?, ?, NOW(), ?, ?, ?, ?, ?)");

            return $stmt->execute([
                $this->full_name,
                $this->email,
                $this->password,
                $this->role,
                $this->num_user,
                $this->facebook_id,
                $this->google_id,
                $this->profile_picture
            ]);
        }
    }

    public function updateProfilePicture($db)
    {
        $stmt = $db->prepare("UPDATE user SET profile_picture = ? WHERE id_user = ?");
        return $stmt->execute([$this->profile_picture, $this->id_user]);
    }

    public static function findByFacebookId($db, $facebookId)
    {
        $stmt = $db->prepare("SELECT * FROM user WHERE facebook_id = ?");
        $stmt->execute([$facebookId]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($data) {
            return new User(
                $data['id_user'],
                $data['full_name'],
                $data['email'],
                $data['password'],
                $data['date_inscription'],
                $data['role'],
                $data['num_user'],
                $data['facebook_id'],
                $data['google_id']
            );
        }
        return null;
    }

    public static function findByGoogleId($db, $googleId)
    {
        $stmt = $db->prepare("SELECT * FROM user WHERE google_id = ?");
        $stmt->execute([$googleId]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($data) {
            return new User(
                $data['id_user'],
                $data['full_name'],
                $data['email'],
                $data['password'],
                $data['date_inscription'],
                $data['role'],
                $data['num_user'],
                $data['facebook_id'],
                $data['google_id']
            );
        }
        return null;
    }

    public static function findByEmail($db, $email)
    {
        $stmt = $db->prepare("SELECT * FROM user WHERE email = ?");
        $stmt->execute([$email]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($data) {
            return new User(
                $data['id_user'],
                $data['full_name'],
                $data['email'],
                $data['password'],
                $data['date_inscription'],
                $data['role'],
                $data['num_user'],
                $data['facebook_id'],
                $data['google_id'],
                $data['profile_picture'], // Added this line
                $data['ban_reason'],
                $data['is_verified'] // Added this line
            );
        }
        return null;
    }

    public static function getAll($db)
    {
        $stmt = $db->query("SELECT * FROM user");
        $users = [];
        while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $users[] = new User(
                $data['id_user'],
                $data['full_name'],
                $data['email'],
                $data['password'],
                $data['date_inscription'],
                $data['role'],
                $data['num_user'],
                $data['facebook_id'],
                $data['google_id'],
                $data['profile_picture'] // Added this line
            );
        }
        return $users;
    }
    public static function findAll($db)
    {
        $query = $db->query("SELECT * FROM user");
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function verifyPassword($db, $email, $password)
    {
        $stmt = $db->prepare("SELECT password FROM user WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        return $user && password_verify($password, $user['password']);
    }

    public static function deleteByEmail($db, $email)
    {
        $stmt = $db->prepare("DELETE FROM user WHERE email = ?");
        return $stmt->execute([$email]);
    }




    // Dans ton contrôleur ou modèle (User.php)
    // Ensure this logic is implemented in a controller or a method, not directly in the class body.
    public static function updateSessionDateInscription($userModel, $session)
    {
        $user = $userModel->getUserById($session['user']['id_user']);
        $session['user']['date_inscription'] = $user['date_inscription'];
    }

    public function updateUserInfo($db)
    {
        $sql = "UPDATE user SET full_name = ?, email = ?, num_user = ?, password = ?, profile_picture = ? WHERE id_user = ?";
        $stmt = $db->prepare($sql);
        return $stmt->execute([
            $this->full_name,
            $this->email,
            $this->num_user,
            $this->password,
            $this->profile_picture,
            $this->id_user
        ]);
    }


    public static function emailExists($db, $email)
    {
        $stmt = $db->prepare("SELECT id_user FROM utilisateur WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch() !== false;
    }




    /**
     * Get the value of date_inscription
     */
    public function getDateInscription()
    {
        return $this->date_inscription;
    }

    /**
     * Set the value of date_inscription
     */
    public function setDateInscription($date_inscription): self
    {
        $this->date_inscription = $date_inscription;

        return $this;
    }
}