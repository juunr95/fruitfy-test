<?php

namespace App\Exceptions;

use Exception;

class FeatureDisabledException extends Exception
{
    protected $featureKey;
    protected $featureMessage;

    public function __construct(string $featureKey, string $featureMessage = null, int $code = 403)
    {
        $this->featureKey = $featureKey;
        $this->featureMessage = $featureMessage;
        
        $message = $featureMessage ?? "A funcionalidade '{$featureKey}' está temporariamente desabilitada.";
        
        parent::__construct($message, $code);
    }

    public function getFeatureKey(): string
    {
        return $this->featureKey;
    }

    public function getFeatureMessage(): ?string
    {
        return $this->featureMessage;
    }

    /**
     * Render the exception as an HTTP response.
     */
    public function render($request)
    {
        if ($request->expectsJson()) {
            return response()->json([
                'error' => 'Feature Disabled',
                'message' => $this->getMessage(),
                'feature_key' => $this->featureKey,
            ], $this->getCode());
        }

        return redirect()->back()->with('warning', $this->getMessage());
    }
} 