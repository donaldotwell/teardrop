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

        // Generate new challenge
        $this->generateUrlChallenge($request);

        $url       = session('bot_challenge_url');
        $positions = session('bot_challenge_positions');
        $maskedUrl = $this->createMaskedUrl($url, $positions);

        $failedAttempts    = session('bot_challenge_failed_attempts', 0);
        $remainingAttempts = 3 - $failedAttempts;

        return view('bot-challenge.show', compact('maskedUrl', 'remainingAttempts'));
    }

    /**
     * Show locked out page
     */
    public function locked()
    {
        $lockedUntil = session('bot_challenge_locked_until');

        if (!$lockedUntil || time() >= $lockedUntil) {
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
            'answer' => 'required|string|size:6',
        ]);

        $correctAnswers     = session('bot_challenge_answers');
        $challengeStartedAt = session('bot_challenge_started_at');

        // Validate challenge exists
        if (!$correctAnswers || !$challengeStartedAt || count($correctAnswers) !== 6) {
            return back()->withErrors(['error' => 'Challenge expired. Please try again.']);
        }

        // Anti-bot: at least 3 seconds must have passed
        if (time() - $challengeStartedAt < 3) {
            $this->recordFailedAttempt();
            return back()->withErrors(['error' => 'Too fast! Please take your time.']);
        }

        // Case-insensitive comparison of the 6-character answer
        $userChars    = str_split(strtolower($validated['answer']));
        $correctChars = array_map('strtolower', $correctAnswers);

        if ($userChars !== $correctChars) {
            $this->recordFailedAttempt();

            $remaining = 3 - session('bot_challenge_failed_attempts', 0);

            if ($remaining <= 0) {
                return redirect()->route('bot-challenge.locked');
            }

            return back()->withErrors(['error' => "Incorrect. {$remaining} attempt(s) remaining."]);
        }

        // Success
        session(['bot_challenge_passed' => true]);

        session()->forget([
            'bot_challenge_url',
            'bot_challenge_positions',
            'bot_challenge_answers',
            'bot_challenge_started_at',
            'bot_challenge_failed_attempts',
        ]);

        $intendedUrl = session()->pull('bot_challenge_intended', route('home'));

        return redirect($intendedUrl)->with('success', 'Verification successful!');
    }

    /**
     * Generate a URL challenge and store answers in session
     */
    protected function generateUrlChallenge(Request $request): void
    {
        $host = $request->getHost();

        if (empty($host) || str_starts_with($host, '127.0.0.1')) {
            $host = strtolower(config('app.name', 'marketplace')) . '.onion';
        }

        if (strlen($host) < 6) {
            $host = $host . '.onion';
        }

        $urlLength = strlen($host);
        $positions = [];
        $attempts  = 0;

        while (count($positions) < 6 && $attempts < 100) {
            $pos  = rand(0, $urlLength - 1);
            $char = $host[$pos];

            if (ctype_alnum($char) && !in_array($pos, $positions)) {
                $positions[] = $pos;
            }
            $attempts++;
        }

        // Fallback: first 6 alphanumeric chars
        if (count($positions) < 6) {
            $positions = [];
            for ($i = 0; $i < $urlLength && count($positions) < 6; $i++) {
                if (ctype_alnum($host[$i])) {
                    $positions[] = $i;
                }
            }
        }

        sort($positions);

        $answers = array_map(fn($p) => $host[$p], $positions);

        session([
            'bot_challenge_url'        => $host,
            'bot_challenge_positions'  => $positions,
            'bot_challenge_answers'    => $answers,
            'bot_challenge_started_at' => time(),
        ]);
    }

    /**
     * Build the masked URL string (blanks shown as _)
     */
    protected function createMaskedUrl(string $url, array $positions): string
    {
        $masked = '';
        for ($i = 0; $i < strlen($url); $i++) {
            $masked .= in_array($i, $positions) ? '_' : $url[$i];
        }
        return $masked;
    }

    /**
     * Record a failed attempt; lock out after 3 failures
     */
    protected function recordFailedAttempt(): void
    {
        $attempts = session('bot_challenge_failed_attempts', 0) + 1;
        session(['bot_challenge_failed_attempts' => $attempts]);

        if ($attempts >= 3) {
            session(['bot_challenge_locked_until' => time() + 1800]);
        }
    }
}
