<?php
/**
 * Unit tests for helper functions.
 *
 * @package CaraTemple\Tests\Unit
 */

declare(strict_types=1);

namespace CaraTemple\Tests\Unit;

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../includes/helpers.php';

final class HelpersTest extends TestCase
{
    public function testFormatRelativeTimeReturnsJustNow(): void
    {
        $now = date('Y-m-d H:i:s');
        $result = format_relative_time($now);
        
        $this->assertSame('à l\'instant', $result);
    }

    public function testFormatRelativeTimeReturnsMinutesAgo(): void
    {
        $fiveMinutesAgo = date('Y-m-d H:i:s', strtotime('-5 minutes'));
        $result = format_relative_time($fiveMinutesAgo);
        
        $this->assertSame('il y a 5 minutes', $result);
    }

    public function testFormatRelativeTimeReturnsHoursAgo(): void
    {
        $twoHoursAgo = date('Y-m-d H:i:s', strtotime('-2 hours'));
        $result = format_relative_time($twoHoursAgo);
        
        $this->assertSame('il y a 2 heures', $result);
    }

    public function testFormatRelativeTimeReturnsDaysAgo(): void
    {
        $threeDaysAgo = date('Y-m-d H:i:s', strtotime('-3 days'));
        $result = format_relative_time($threeDaysAgo);
        
        $this->assertSame('il y a 3 jours', $result);
    }

    public function testHtmlspecialcharsEscapesSpecialCharacters(): void
    {
        $input = '<script>alert("XSS")</script>';
        $expected = '&lt;script&gt;alert(&quot;XSS&quot;)&lt;/script&gt;';
        $result = htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
        
        $this->assertSame($expected, $result);
    }

    public function testHtmlspecialcharsHandlesEmptyString(): void
    {
        $result = htmlspecialchars('', ENT_QUOTES, 'UTF-8');
        
        $this->assertSame('', $result);
    }

    public function testGenerateCsrfTokenReturnsString(): void
    {
        $token = generate_csrf_token('test_action');
        
        $this->assertIsString($token);
        $this->assertNotEmpty($token);
    }

    public function testValidateCsrfTokenReturnsTrueForValidToken(): void
    {
        $key = 'test_action_' . time();
        $token = generate_csrf_token($key);
        
        $result = validate_csrf_token($key, $token);
        
        $this->assertTrue($result);
    }

    public function testValidateCsrfTokenReturnsFalseForInvalidToken(): void
    {
        $key = 'test_action_' . time();
        $invalidToken = 'invalid_token_12345';
        
        $result = validate_csrf_token($key, $invalidToken);
        
        $this->assertFalse($result);
    }

    public function testFilterVarSanitizesEmail(): void
    {
        $input = '  user@EXAMPLE.COM  ';
        $result = filter_var(trim($input), FILTER_SANITIZE_EMAIL);
        $result = strtolower($result);
        
        $this->assertSame('user@example.com', $result);
    }

    public function testRegexValidatesUsername(): void
    {
        $validUsername = 'user_123';
        $invalidUsername = 'user@name';
        
        $this->assertMatchesRegularExpression('/^[A-Za-z0-9_]{3,20}$/', $validUsername);
        $this->assertDoesNotMatchRegularExpression('/^[A-Za-z0-9_]{3,20}$/', $invalidUsername);
    }
}
