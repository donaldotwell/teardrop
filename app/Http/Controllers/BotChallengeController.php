<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class BotChallengeController extends Controller
{
    /**
     * Show the bot challenge page
     */
    public function show()
    {
        // Check if locked out
        $lockedUntil = session('bot_challenge_locked_until');
        if ($lockedUntil && time() < $lockedUntil) {
            $remainingMinutes = ceil(($lockedUntil - time()) / 60);
            return view('bot-challenge.locked', compact('remainingMinutes'));
        }

        // Generate new math challenge
        $this->generateMathChallenge();

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
            'answer' => 'required|integer',
        ]);

        $answer = (int) $validated['answer'];
        $correctAnswer = session('bot_challenge_answer');
        $challengeStartedAt = session('bot_challenge_started_at');

        // Validate challenge exists
        if (!$correctAnswer || !$challengeStartedAt) {
            return back()->withErrors(['answer' => 'Challenge expired. Please try again.']);
        }

        // Check minimum time (anti-bot: at least 2 seconds)
        $timeTaken = time() - $challengeStartedAt;
        if ($timeTaken < 2) {
            $this->recordFailedAttempt();
            return back()->withErrors(['answer' => 'Too fast! Please take your time.']);
        }

        // Check maximum time (challenge expires after 5 minutes)
        if ($timeTaken > 300) {
            session()->forget(['bot_challenge_answer', 'bot_challenge_started_at', 'bot_challenge_num1', 'bot_challenge_num2']);
            return back()->withErrors(['answer' => 'Challenge expired. Please try again.']);
        }

        // Verify answer
        if ($answer !== $correctAnswer) {
            $this->recordFailedAttempt();

            $failedAttempts = session('bot_challenge_failed_attempts', 0);
            $remainingAttempts = 3 - $failedAttempts;

            if ($remainingAttempts <= 0) {
                return redirect()->route('bot-challenge.locked');
            }

            return back()->withErrors(['answer' => "Incorrect answer. You have {$remainingAttempts} attempts remaining."]);
        }

        // Success! Mark as passed
        session([
            'bot_challenge_passed_at' => time(),
        ]);

        // Clear challenge data
        session()->forget([
            'bot_challenge_answer',
            'bot_challenge_started_at',
            'bot_challenge_num1',
            'bot_challenge_num2',
            'bot_challenge_failed_attempts',
        ]);

        // Redirect to intended URL or home
        $intendedUrl = session()->pull('bot_challenge_intended', route('home'));

        return redirect($intendedUrl)->with('success', 'Verification successful!');
    }

    /**
     * Generate the math challenge image
     */
    public function image()
    {
        $num1 = session('bot_challenge_num1');
        $num2 = session('bot_challenge_num2');

        if (!$num1 || !$num2) {
            abort(404);
        }

        // Create image
        $width = 200;
        $height = 80;

        $image = imagecreate($width, $height);

        // Colors
        $bgColor = imagecolorallocate($image, 255, 255, 255);
        $textColor = imagecolorallocate($image, 50, 50, 50);
        $lineColor = imagecolorallocate($image, 200, 200, 200);
        $dotColor = imagecolorallocate($image, 150, 150, 150);

        // Add noise - random lines
        for ($i = 0; $i < 5; $i++) {
            imageline($image,
                rand(0, $width), rand(0, $height),
                rand(0, $width), rand(0, $height),
                $lineColor
            );
        }

        // Add noise - random dots
        for ($i = 0; $i < 100; $i++) {
            imagesetpixel($image, rand(0, $width), rand(0, $height), $dotColor);
        }

        // Generate the math question text
        $text = "{$num1} + {$num2} = ?";

        // Use built-in font (font 5 is largest built-in)
        $font = 5;
        $textWidth = imagefontwidth($font) * strlen($text);
        $textHeight = imagefontheight($font);

        // Center the text
        $x = ($width - $textWidth) / 2;
        $y = ($height - $textHeight) / 2;

        // Draw text with slight offset for each character (distortion effect)
        $currentX = $x;
        foreach (str_split($text) as $char) {
            $offsetY = rand(-3, 3);
            imagestring($image, $font, $currentX, $y + $offsetY, $char, $textColor);
            $currentX += imagefontwidth($font);
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
     * Generate a new math challenge and store in session
     */
    protected function generateMathChallenge()
    {
        $num1 = rand(1, 15);
        $num2 = rand(1, 15);
        $answer = $num1 + $num2;

        session([
            'bot_challenge_num1' => $num1,
            'bot_challenge_num2' => $num2,
            'bot_challenge_answer' => $answer,
            'bot_challenge_started_at' => time(),
        ]);
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
