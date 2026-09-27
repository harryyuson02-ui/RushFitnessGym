<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\SvgWriter;
use Illuminate\Http\Response;
use Illuminate\View\View;

class SubscriptionQrController extends Controller
{
    public function image(string $token): Response
    {
        $subscription = Subscription::where('access_token', $token)->firstOrFail();
        $result = (new SvgWriter)->write(new QrCode(
            data: route('admin.access.verify', $subscription->access_token),
            errorCorrectionLevel: ErrorCorrectionLevel::High,
            size: 320,
            margin: 16,
        ));

        return response($result->getString())
            ->header('Content-Type', $result->getMimeType())
            ->header('Cache-Control', 'private, max-age=300');
    }

    public function verify(string $token): View
    {
        $subscription = Subscription::query()
            ->with(['member.coach', 'plan'])
            ->where('access_token', $token)
            ->firstOrFail();

        return view('admin.members.verify', compact('subscription'));
    }
}
