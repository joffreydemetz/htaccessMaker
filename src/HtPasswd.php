<?php

/**
 * (c) Joffrey Demetz <joffrey.demetz@gmail.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace JDZ\HtaccessMaker;

/**
 * HtPasswd
 * 
 * This class is used to manage users for .htpasswd files.
 * It allows you to add users with their passwords,
 * and it provides a method to convert the user data into a string format suitable for .ht
 * passwd files.
 * 
 * It uses APR1-MD5 encryption for passwords, which is compatible with Apache's htpasswd utility.
 * 
 * @author  Joffrey Demetz <joffrey.demetz@gmail.com>
 */
class HtPasswd
{
  protected array $users = [];

  public function toString(): string
  {
    $str = '';

    foreach ($this->users as $user) {
      $str .= '# ' . $user['password'] . "\n";
      $str .= $user['name'] . ':' . $user['encryptedPassword'] . "\n";
    }

    return $str;
  }

  public function addUser(string $name, string $password): self
  {
    $this->users[] = [
      'name' => $name,
      'password' => $password,
      // Use APR1-MD5 encryption for compatibility with Apache's htpasswd
      'encryptedPassword' => $this->crypt_apr1_md5($password),
    ];
    return $this;
  }

  /**
   * APR1-MD5 encryption method (windows compatible)
   */
  private function crypt_apr1_md5(string $plainpasswd): string
  {
    $salt = substr(str_shuffle("abcdefghijklmnopqrstuvwxyz0123456789"), 0, 8);
    $len = strlen($plainpasswd);
    $text = $plainpasswd . '$apr1$' . $salt;
    $bin = pack("H32", md5($plainpasswd . $salt . $plainpasswd));
    for ($i = $len; $i > 0; $i -= 16) {
      $text .= substr($bin, 0, min(16, $i));
    }
    for ($i = $len; $i > 0; $i >>= 1) {
      $text .= ($i & 1) ? chr(0) : $plainpasswd[0];
    }
    $bin = pack("H32", md5($text));

    for ($i = 0; $i < 1000; $i++) {
      $new = ($i & 1) ? $plainpasswd : $bin;
      if ($i % 3) $new .= $salt;
      if ($i % 7) $new .= $plainpasswd;
      $new .= ($i & 1) ? $bin : $plainpasswd;
      $bin = pack("H32", md5($new));
    }

    $tmp = '';
    for ($i = 0; $i < 5; $i++) {
      $k = $i + 6;
      $j = $i + 12;
      if ($j == 16) $j = 5;
      $tmp = $bin[$i] . $bin[$k] . $bin[$j] . $tmp;
    }

    $tmp = chr(0) . chr(0) . $bin[11] . $tmp;
    $tmp = strtr(strrev(substr(base64_encode($tmp), 2)), "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/", "./0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz");
    return "$" . "apr1" . "$" . $salt . "$" . $tmp;
  }
}
