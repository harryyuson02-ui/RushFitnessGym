<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\SvgWriter;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class SubscriptionQrController extends Controller
{
    public function image(Request $request, string $token): Response
    {
        $subscription = Subscription::query()
            ->with('member')
            ->active()
            ->where('access_token', $token)
            ->firstOrFail();

        abort_unless(
            $request->user()->isAdmin() || $subscription->member->user_id === $request->user()->id,
            403,
        );

        $result = (new SvgWriter)->write(new QrCode(
            data: route('admin.access.verify', $subscription->access_token),
            errorCorrectionLevel: ErrorCorrectionLevel::High,
            size: 320,
            margin: 16,
        ));
        $xml = $result->getXml();
        $outerSize = (int) $xml['width'];
        $canvasHeight = $outerSize + 64;
        $center = $outerSize / 2;

        $xml['height'] = $canvasHeight.'px';
        $xml['viewBox'] = '0 0 '.$outerSize.' '.$canvasHeight;
        $xml->rect[0]['height'] = (string) $canvasHeight;

        $brandBackground = $xml->addChild('rect');
        $brandBackground->addAttribute('x', (string) ($center - 52));
        $brandBackground->addAttribute('y', (string) ($center - 30));
        $brandBackground->addAttribute('width', '104');
        $brandBackground->addAttribute('height', '60');
        $brandBackground->addAttribute('rx', '8');
        $brandBackground->addAttribute('fill', '#ffffff');

        $brand = $xml->addChild('text', 'RUSH');
        $brand->addAttribute('x', (string) $center);
        $brand->addAttribute('y', (string) ($center - 2));
        $brand->addAttribute('text-anchor', 'middle');
        $brand->addAttribute('font-family', 'Arial, sans-serif');
        $brand->addAttribute('font-size', '23');
        $brand->addAttribute('font-weight', '900');
        $brand->addAttribute('letter-spacing', '2');
        $brand->addAttribute('fill', '#65a30d');

        $brandSubtitle = $xml->addChild('text', 'FITNESS GYM');
        $brandSubtitle->addAttribute('x', (string) $center);
        $brandSubtitle->addAttribute('y', (string) ($center + 16));
        $brandSubtitle->addAttribute('text-anchor', 'middle');
        $brandSubtitle->addAttribute('font-family', 'Arial, sans-serif');
        $brandSubtitle->addAttribute('font-size', '8');
        $brandSubtitle->addAttribute('font-weight', '700');
        $brandSubtitle->addAttribute('letter-spacing', '2');
        $brandSubtitle->addAttribute('fill', '#18181b');

        $memberName = $xml->addChild('text', htmlspecialchars($subscription->member->full_name, ENT_XML1 | ENT_COMPAT, 'UTF-8'));
        $memberName->addAttribute('x', (string) $center);
        $memberName->addAttribute('y', (string) ($outerSize + 28));
        $memberName->addAttribute('text-anchor', 'middle');
        $memberName->addAttribute('font-family', 'Arial, sans-serif');
        $memberName->addAttribute('font-size', '17');
        $memberName->addAttribute('font-weight', '700');
        $memberName->addAttribute('fill', '#18181b');

        $memberCode = $xml->addChild('text', htmlspecialchars($subscription->member->member_code, ENT_XML1 | ENT_COMPAT, 'UTF-8'));
        $memberCode->addAttribute('x', (string) $center);
        $memberCode->addAttribute('y', (string) ($outerSize + 48));
        $memberCode->addAttribute('text-anchor', 'middle');
        $memberCode->addAttribute('font-family', 'Arial, sans-serif');
        $memberCode->addAttribute('font-size', '11');
        $memberCode->addAttribute('letter-spacing', '1');
        $memberCode->addAttribute('fill', '#52525b');

        $response = response($result->getString())
            ->header('Content-Type', $result->getMimeType())
            ->header('Cache-Control', 'private, max-age=300');

        if ($request->boolean('download')) {
            $response->header('Content-Disposition', 'attachment; filename="'.$subscription->member->member_code.'-access-qr.svg"');
        }

        return $response;
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
