<?php
declare(strict_types=1);
/**
 * Paragon Initiative Enterprises.
 *
 * @author  Scott Arciszewski   <scott@paragonie.com>.
 * @author  EasyDB Contributors <https://github.com/paragonie/easydb/graphs/contributors>
 *
 * @link    <https://github.com/paragonie/easydb> Github Repository.
 * @license <https://github.com/paragonie/easydb/blob/master/LICENSE> MIT License.
 *
 * @package ParagonIE\EasyDB
 */

namespace ParagonIE\EasyDB;

use PDO;
use PDOException;

/**
 * Factory.
 */
abstract class Factory
{

    /**
     * Create a new EasyDB object based on PDO constructors.
     *
     * @param string      $dsn      The dns connection string.
     * @param string|null $username The database username.
     * @param string|null $password The database password.
     * @param array       $options  The database options.
     *
     * @throws Exception\ConstructorFailed If the PDO connection could
     *                                     not be created.
     *
     * @return EasyDB Return the EasyDB class.
     */
    public static function create(
        string $dsn,
        string $username = \null,
        string $password = \null,
        array  $options = []
    ): EasyDB {
        if (\is_null($username)) {
            $username = '';
        }
        if (\is_null($password)) {
            $password = '';
        }
        $dbEngine = '';
        // Get the pdo driver.
        if (\strpos($dsn, ':') !== \false) {
            // Explode the dns string into parts.
            $dbEngine = \explode(':', $dsn)[0];
        }
        /** @var string $post_query */
        $post_query = '';
        switch ($dbEngine) {
            case 'mysql':
                // Set the charset.
                if (\strpos($dsn, ';charset=') === \false) {
                    $dsn .= ';charset=utf8mb4';
                }
                break;
            case 'pgsql':
                // Set names unicode.
                $post_query = 'SET NAMES UNICODE';
                break;
        }
        try {
            // Try to create a new connection.
            $pdo = new PDO($dsn, $username, $password, $options);
        } catch (PDOException $e) {
            // Suppress errors to prevent sensitive information from being displayed.
            throw new Exception\ConstructorFailed(
                'Could not create a PDO connection. Please check your username and password.'
            );
        }
        // Execute post query if it is not empty.
        if (!empty($post_query)) {
            $pdo->query($post_query);
        }
        // Return the EasyDB classs.
        return new EasyDB($pdo, $dbEngine, $options);
    }
}
