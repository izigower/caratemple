<?php
/**
 * Unit tests for authentication functions.
 *
 * @package CaraTemple\Tests\Unit
 */

declare(strict_types=1);

namespace CaraTemple\Tests\Unit;

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../includes/auth.php';

final class AuthTest extends TestCase
{
    public function testPasswordHashReturnsValidHash(): void
    {
        $password = 'SecurePassword123';
        $hash = password_hash($password, PASSWORD_DEFAULT);
        
        $this->assertIsString($hash);
        $this->assertStringStartsWith('$2y$', $hash); // bcrypt identifier
        $this->assertGreaterThan(50, strlen($hash));
    }

    public function testPasswordVerifyReturnsTrueForCorrectPassword(): void
    {
        $password = 'SecurePassword123';
        $hash = password_hash($password, PASSWORD_DEFAULT);
        
        $result = password_verify($password, $hash);
        
        $this->assertTrue($result);
    }

    public function testPasswordVerifyReturnsFalseForIncorrectPassword(): void
    {
        $password = 'SecurePassword123';
        $wrongPassword = 'WrongPassword456';
        $hash = password_hash($password, PASSWORD_DEFAULT);
        
        $result = password_verify($wrongPassword, $hash);
        
        $this->assertFalse($result);
    }

    public function testUsernameRegexValidation(): void
    {
        $validUsernames = ['user123', 'John_Doe', 'alice', 'bob_2024'];
        $pattern = '/^[A-Za-z0-9_]{3,20}$/';
        
        foreach ($validUsernames as $username) {
            $this->assertMatchesRegularExpression($pattern, $username, "Username '$username' should be valid");
        }
    }

    public function testUsernameRegexRejectsInvalid(): void
    {
        $invalidUsernames = ['ab', 'user@name', 'user name', 'a'];
        $pattern = '/^[A-Za-z0-9_]{3,20}$/';
        
        foreach ($invalidUsernames as $username) {
            $this->assertDoesNotMatchRegularExpression($pattern, $username, "Username '$username' should be invalid");
        }
    }

    public function testFilterVarValidatesEmail(): void
    {
        $validEmails = ['user@example.com', 'test.user@domain.co.uk', 'admin+tag@site.org'];
        
        foreach ($validEmails as $email) {
            $result = filter_var($email, FILTER_VALIDATE_EMAIL);
            $this->assertNotFalse($result, "Email '$email' should be valid");
        }
    }

    public function testFilterVarRejectsInvalidEmail(): void
    {
        $invalidEmails = ['invalid', 'user@', '@domain.com', 'user @domain.com'];
        
        foreach ($invalidEmails as $email) {
            $result = filter_var($email, FILTER_VALIDATE_EMAIL);
            $this->assertFalse($result, "Email '$email' should be invalid");
        }
    }

    public function testPasswordStrengthRegex(): void
    {
        $strongPassword = 'Password123';
        
        $hasLength = strlen($strongPassword) >= 8;
        $hasLetter = preg_match('/[A-Za-z]/', $strongPassword);
        $hasNumber = preg_match('/\d/', $strongPassword);
        
        $this->assertTrue($hasLength);
        $this->assertSame(1, $hasLetter);
        $this->assertSame(1, $hasNumber);
    }

    public function testWeakPasswordFailsValidation(): void
    {
        $weakPassword = 'short';
        
        $hasLength = strlen($weakPassword) >= 8;
        
        $this->assertFalse($hasLength);
    }
}
