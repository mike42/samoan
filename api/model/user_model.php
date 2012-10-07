<?php
class user_model {
	private static $template;
	
	public static function init() {
		core::loadClass('database');
		
		self::$template = array(
			'user_id',
			'user_name' => '',
			'user_pass' => '',
			'user_salt' => '',
			'user_token' => '',
			'user_email' => '',
			'user_email_confirmed' => '',
			'user_created' => '',
			'user_role' => '');
	}
	
	/**
	 * Get user by email address or username
	 */
	public static function getByNameOrEmail($name_or_email) {
		$query = "SELECT * FROM {TABLE}user WHERE user_email = '%s' or user_name = '%s';";
		if($row = database::retrieve($query, 1, $name_or_email, $name_or_email)) {
			return database::row_from_template($row, user_model::$template);
		} else {
			return false;
		}
	}
	
	/**
	 * Add new user to database
	 * 
	 * @param string $user_name		User's login name
	 * @param string $user_email	Email address of this user
	 * @param string $password		Password to use (will be hashed and salted)
	 */
	public static function insert($user_name, $user_email, $password) {
		if($user = self::getByNameOrEmail($user_name) || $user = self::getByNameOrEmail($user_email)) {
			/* Skip if a user already has this name or email */
			return false;
		}
		
		if(!filter_var($user_email, FILTER_VALIDATE_EMAIL) || !(strpos($user_name, '@') === false)) {
			/* Also skip if email is bad or username looks email-ish */
			return false;
		}
		
		/* Fill in user details */
		$user = self::$template;
		$user['user_name'] = $user_name;
		$user['user_email'] = $user_email;
		$user['user_salt'] = self::gen_salt();
		$user['user_pass'] = self::gen_password_encoded($password, $user['user_salt']);

		/* Insert */
		$sql = "INSERT INTO {TABLE}user (user_id, user_name, user_pass, user_salt, user_token, user_email, user_email_confirmed, user_created, user_role) " .
				"VALUES (NULL , '%s', '%s', '%s', '', '%s', '0', CURRENT_TIMESTAMP , 'user');";
		return database::retrieve($sql, 2, $user['user_name'], $user['user_pass'], $user['user_salt'], $user['user_email']);
	}

	public static function verifyLogin($user_name, $password) {
		if($user = self::getByNameOrEmail($user_name)) {
			if($user['user_pass'] == self::gen_password_encoded($password, $user['user_salt'])) {
				return true;
			}
		}
		/* No such user or wrong password*/
		return false;
	}
	
	
	private static function gen_password_encoded($password_plaintext, $salt) {
		/* Join password and salt, then hash them for the password field (or to compare with a password field) */
		$password_plaintext = trim($password_plaintext);
		return hash('sha256', $salt.":".$password_plaintext);
	}
	
	private static function gen_salt() {
		/* Make a salt for our string (hash some random data) */
		return hash('sha256', self::gen_random_chars(1024));
	}
	
	private static function gen_token() {
		/* Tokens look the same as salts at the moment */
		return self::gen_salt();
	}
	
	private static function gen_random_chars($len, $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ!@#$%^*()1234567890-_+=,.<>") {
		/* Return random characters from a string, to a specified length (use for generating passwords, salts, tokens */
		$char_count = strlen($chars);
		for($i = 0; $i < $len; $i++)	{
			$val[$i] = substr($chars, rand(0,$char_count), 1);
		}
		return implode("", $val);
	}
}
?>