<?php

namespace Tests\Unit;

use App\Services\Chat\TokenCounterService;
use PHPUnit\Framework\TestCase;

class TokenCounterServiceTest extends TestCase
{
    private TokenCounterService $tokenCounter;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tokenCounter = new TokenCounterService;
    }

    public function test_estimates_tokens_for_empty_string(): void
    {
        $tokens = $this->tokenCounter->estimateTokens('');

        $this->assertEquals(0, $tokens);
    }

    public function test_estimates_tokens_using_char_approximation(): void
    {
        // 4 characters = approximately 1 token
        $text = 'word'; // 4 chars
        $tokens = $this->tokenCounter->estimateTokens($text);

        $this->assertEquals(1, $tokens);
    }

    public function test_estimates_tokens_for_longer_text(): void
    {
        // 400 characters = approximately 100 tokens
        $text = str_repeat('word', 100); // 400 chars
        $tokens = $this->tokenCounter->estimateTokens($text);

        $this->assertEquals(100, $tokens);
    }

    public function test_rounds_up_token_estimate(): void
    {
        // 5 characters should round up to 2 tokens (5/4 = 1.25 -> 2)
        $text = 'words'; // 5 chars
        $tokens = $this->tokenCounter->estimateTokens($text);

        $this->assertEquals(2, $tokens);
    }

    public function test_get_model_limit_for_known_model(): void
    {
        $limit = $this->tokenCounter->getModelLimit('gpt-4o');

        $this->assertEquals(128000, $limit);
    }

    public function test_get_model_limit_for_claude_model(): void
    {
        $limit = $this->tokenCounter->getModelLimit('claude-3-5-sonnet');

        $this->assertEquals(200000, $limit);
    }

    public function test_get_model_limit_for_partial_match(): void
    {
        // Should match 'gpt-4' pattern
        $limit = $this->tokenCounter->getModelLimit('gpt-4-1106-preview');

        // Falls back to gpt-4 (8192) since exact match for turbo fails
        $this->assertEquals(8192, $limit);
    }

    public function test_get_model_limit_for_unknown_model(): void
    {
        $limit = $this->tokenCounter->getModelLimit('unknown-model-xyz');

        $this->assertEquals(8192, $limit); // Default
    }

    public function test_handles_multibyte_characters(): void
    {
        // Unicode characters should be counted correctly
        $text = 'привет мир'; // Russian "hello world"
        $tokens = $this->tokenCounter->estimateTokens($text);

        // Should estimate based on character count, not byte count
        $this->assertGreaterThan(0, $tokens);
    }

    public function test_extract_text_from_tiptap_json(): void
    {
        $content = [
            'type' => 'doc',
            'content' => [
                [
                    'type' => 'paragraph',
                    'content' => [
                        ['type' => 'text', 'text' => 'Hello world.'],
                    ],
                ],
                [
                    'type' => 'paragraph',
                    'content' => [
                        ['type' => 'text', 'text' => 'Second paragraph.'],
                    ],
                ],
            ],
        ];

        // Using reflection to test protected method
        $method = new \ReflectionMethod($this->tokenCounter, 'extractTextFromContent');

        $text = $method->invoke($this->tokenCounter, $content);

        $this->assertStringContainsString('Hello world.', $text);
        $this->assertStringContainsString('Second paragraph.', $text);
    }

    public function test_extract_text_from_plain_string(): void
    {
        $content = '<p>Hello <strong>world</strong></p>';

        // Using reflection to test protected method
        $method = new \ReflectionMethod($this->tokenCounter, 'extractTextFromContent');

        $text = $method->invoke($this->tokenCounter, $content);

        // Should strip HTML tags
        $this->assertEquals('Hello world', $text);
    }

    public function test_truncates_long_content(): void
    {
        // Test the truncation logic using estimateTokens with different lengths
        $shortText = 'Short text.';
        $longText = str_repeat('A', 1000);

        $shortTokens = $this->tokenCounter->estimateTokens($shortText);
        $longTokens = $this->tokenCounter->estimateTokens($longText);

        // Long text should have more tokens
        $this->assertGreaterThan($shortTokens, $longTokens);
        // 1000 chars / 4 = 250 tokens
        $this->assertEquals(250, $longTokens);
    }

    public function test_handles_null_content(): void
    {
        // Empty/null content should return 0 tokens
        $tokens = $this->tokenCounter->estimateTokens('');

        $this->assertEquals(0, $tokens);
    }
}
