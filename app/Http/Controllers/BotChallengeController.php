<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class BotChallengeController extends Controller
{
    /**
     * Show the bot challenge page
     */
    public function show(Request $request)
    {
        // Check if locked out
        $lockedUntil = session('bot_challenge_locked_until');
        if ($lockedUntil && time() < $lockedUntil) {
            $remainingMinutes = ceil(($lockedUntil - time()) / 60);
            return view('bot-challenge.locked', compact('remainingMinutes'));
        }

        // Generate new URL challenge
        $this->generateUrlChallenge($request);

        $failedAttempts = session('bot_challenge_failed_attempts', 0);
        $remainingAttempts = 3 - $failedAttempts;

        return view('bot-challenge.show', compact('remainingAttempts'));
    }

    /**
     * Show locked out page
     */
    public function locked()
    {
        $lockedUntil = session('bot_challenge_locked_until');

        if (!$lockedUntil || time() >= $lockedUntil) {
            // Not locked out anymore
            session()->forget(['bot_challenge_locked_until', 'bot_challenge_failed_attempts']);
            return redirect()->route('bot-challenge');
        }

        $remainingMinutes = ceil(($lockedUntil - time()) / 60);
        return view('bot-challenge.locked', compact('remainingMinutes'));
    }

    /**
     * Verify the bot challenge answer
     */
    public function verify(Request $request)
    {
        // Check if locked out
        $lockedUntil = session('bot_challenge_locked_until');
        if ($lockedUntil && time() < $lockedUntil) {
            return redirect()->route('bot-challenge.locked');
        }

        $validated = $request->validate([
            'char_0' => 'required|string|size:1',
            'char_1' => 'required|string|size:1',
            'char_2' => 'required|string|size:1',
            'char_3' => 'required|string|size:1',
            'char_4' => 'required|string|size:1',
            'char_5' => 'required|string|size:1',
        ]);

        $correctAnswers = session('bot_challenge_answers');
        $challengeStartedAt = session('bot_challenge_started_at');

        // Validate challenge exists
        if (!$correctAnswers || !$challengeStartedAt || count($correctAnswers) !== 6) {
            return back()->withErrors(['error' => 'Challenge expired. Please try again.']);
        }

        // Check minimum time (anti-bot: at least 3 seconds)
        $timeTaken = time() - $challengeStartedAt;
        if ($timeTaken < 3) {
            $this->recordFailedAttempt();
            return back()->withErrors(['error' => 'Too fast! Please take your time.']);
        }

        // Verify all answers (case-insensitive)
        $userAnswers = [
            strtolower($validated['char_0']),
            strtolower($validated['char_1']),
            strtolower($validated['char_2']),
            strtolower($validated['char_3']),
            strtolower($validated['char_4']),
            strtolower($validated['char_5']),
        ];

        $allCorrect = true;
        foreach ($userAnswers as $index => $userAnswer) {
            if ($userAnswer !== strtolower($correctAnswers[$index])) {
                $allCorrect = false;
                break;
            }
        }

        if (!$allCorrect) {
            $this->recordFailedAttempt();

            $failedAttempts = session('bot_challenge_failed_attempts', 0);
            $remainingAttempts = 3 - $failedAttempts;

            if ($remainingAttempts <= 0) {
                return redirect()->route('bot-challenge.locked');
            }

            return back()->withErrors(['error' => "Incorrect answer. You have {$remainingAttempts} attempts remaining."]);
        }

        // Success! Mark as passed (session-based, no expiry)
        session([
            'bot_challenge_passed' => true,
        ]);

        // Clear challenge data
        session()->forget([
            'bot_challenge_url',
            'bot_challenge_positions',
            'bot_challenge_answers',
            'bot_challenge_started_at',
            'bot_challenge_failed_attempts',
        ]);

        // Redirect to intended URL or home
        $intendedUrl = session()->pull('bot_challenge_intended', route('home'));

        return redirect($intendedUrl)->with('success', 'Verification successful!');
    }

    /**
     * Generate the URL challenge image
     */
    public function image()
    {
        $url = session('bot_challenge_url');
        $positions = session('bot_challenge_positions');

        if (!$url || !$positions || count($positions) !== 6) {
            abort(404);
        }

        // Create masked URL for display
        $maskedUrl = $this->createMaskedUrl($url, $positions);

        // Create image
        $width = 600;
        $height = 100;

        $image = imagecreatetruecolor($width, $height);

        // Colors
        $bgColor = imagecolorallocate($image, 245, 245, 245);
        $textColor = imagecolorallocate($image, 30, 30, 30);
        $maskColor = imagecolorallocate($image, 200, 50, 50);
        $borderColor = imagecolorallocate($image, 180, 180, 180);

        // Fill background
        imagefilledrectangle($image, 0, 0, $width, $height, $bgColor);

        // Add border
        imagerectangle($image, 0, 0, $width - 1, $height - 1, $borderColor);
        imagerectangle($image, 1, 1, $width - 2, $height - 2, $borderColor);

        // Use built-in font (font 5 is largest built-in)
        $font = 5;
        $charWidth = imagefontwidth($font);
        $charHeight = imagefontheight($font);

        // Calculate starting position to center the text
        $totalWidth = $charWidth * strlen($maskedUrl);
        $startX = ($width - $totalWidth) / 2;
        $y = ($height - $charHeight) / 2;

        // Draw each character
        $currentX = $startX;
        foreach (str_split($maskedUrl) as $char) {
            $color = ($char === '_') ? $maskColor : $textColor;
            $offsetY = rand(-2, 2); // Slight vertical randomness
            imagestring($image, $font, $currentX, $y + $offsetY, $char, $color);
            $currentX += $charWidth;
        }

        // Output image
        header('Content-Type: image/png');
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');

        imagepng($image);
        imagedestroy($image);
        exit;
    }

    /**
     * Generate a new URL challenge and store in session
     */
    protected function generateUrlChallenge(Request $request)
    {
        // Get the host from request
        $host = $request->getHost();

        // Fallback to config app name if host is localhost or empty
        if (empty($host) || str_starts_with($host, '127.0.0.1')) {
            $host = strtolower(config('app.name', 'marketplace')) . '.onion';
        }

        // Pick 6 random positions from the URL
        $urlLength = strlen($host);

        // Ensure we have enough characters
        if ($urlLength < 6) {
            // Pad with .onion if too short
            $host = $host . '.onion';
            $urlLength = strlen($host);
        }

        // Generate 6 unique random positions
        $positions = [];
        $attempts = 0;
        $maxAttempts = 100;

        while (count($positions) < 6 && $attempts < $maxAttempts) {
            $pos = rand(0, $urlLength - 1);
            $char = $host[$pos];

            // Only pick alphanumeric characters
            if (ctype_alnum($char) && !in_array($pos, $positions)) {
                $positions[] = $pos;
            }
            $attempts++;
        }

        // If we couldn't get 6 positions, fallback to first 6 alphanumeric chars
        if (count($positions) < 6) {
            $positions = [];
            for ($i = 0; $i < $urlLength && count($positions) < 6; $i++) {
                if (ctype_alnum($host[$i])) {
                    $positions[] = $i;
                }
            }
        }

        // Sort positions for easier verification
        sort($positions);

        // Extract the characters at these positions
        $answers = [];
        foreach ($positions as $pos) {
            $answers[] = $host[$pos];
        }

        session([
            'bot_challenge_url' => $host,
            'bot_challenge_positions' => $positions,
            'bot_challenge_answers' => $answers,
            'bot_challenge_started_at' => time(),
        ]);
    }

    /**
     * Create a masked URL string for display
     */
    protected function createMaskedUrl(string $url, array $positions): string
    {
        $masked = '';
        for ($i = 0; $i < strlen($url); $i++) {
            if (in_array($i, $positions)) {
                $masked .= '_';
            } else {
                $masked .= $url[$i];
            }
        }
        return $masked;
    }

    /**
     * Record a failed attempt and lock out if necessary
     */
    protected function recordFailedAttempt()
    {
        $attempts = session('bot_challenge_failed_attempts', 0) + 1;
        session(['bot_challenge_failed_attempts' => $attempts]);

        // Lock out after 3 failed attempts for 30 minutes
        if ($attempts >= 3) {
            session(['bot_challenge_locked_until' => time() + (30 * 60)]);
        }
    }
}
