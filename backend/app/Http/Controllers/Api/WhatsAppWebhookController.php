<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WhatsAppConversation;
use App\Models\WhatsAppMessage;
use App\Models\WhatsAppOrder;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WhatsAppWebhookController extends Controller
{
    /**
     * Verify webhook (required by WhatsApp)
     */
    public function verify(Request $request)
    {
        $mode = $request->query('hub_mode');
        $token = $request->query('hub_verify_token');
        $challenge = $request->query('hub_challenge');

        $verifyToken = config('services.whatsapp.verify_token', 'ichri_whatsapp_verify_token');

        if ($mode === 'subscribe' && $token === $verifyToken) {
            Log::info('WhatsApp webhook verified');
            return response($challenge, 200)->header('Content-Type', 'text/plain');
        }

        Log::warning('WhatsApp webhook verification failed', [
            'mode' => $mode,
            'token' => $token,
        ]);

        return response('Forbidden', 403);
    }

    /**
     * Handle incoming webhook events from WhatsApp
     */
    public function webhook(Request $request)
    {
        try {
            Log::info('WhatsApp webhook received', $request->all());

            $data = $request->all();

            // WhatsApp sends data in a specific format
            if (!isset($data['entry'])) {
                return response()->json(['status' => 'ok']);
            }

            foreach ($data['entry'] as $entry) {
                foreach ($entry['changes'] ?? [] as $change) {
                    if ($change['field'] === 'messages') {
                        $this->handleMessageEvent($change['value']);
                    }
                }
            }

            return response()->json(['status' => 'ok']);

        } catch (\Exception $e) {
            Log::error('WhatsApp webhook error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Always return 200 to WhatsApp to avoid retries
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 200);
        }
    }

    /**
     * Handle message events (inbound messages)
     */
    protected function handleMessageEvent($value)
    {
        if (!isset($value['messages'])) {
            return;
        }

        foreach ($value['messages'] as $message) {
            $this->processInboundMessage($message, $value['metadata']);
        }

        // Handle message status updates (delivered, read, etc.)
        if (isset($value['statuses'])) {
            foreach ($value['statuses'] as $status) {
                $this->updateMessageStatus($status);
            }
        }
    }

    /**
     * Process inbound message
     */
    protected function processInboundMessage($message, $metadata)
    {
        $fromNumber = $message['from'];
        $messageType = $message['type'];

        // Get or create conversation
        $conversation = WhatsAppConversation::firstOrCreate(
            ['phone_number' => $fromNumber],
            [
                'user_id' => $this->findUserByPhone($fromNumber),
                'status' => 'active',
                'platform' => 'whatsapp',
            ]
        );

        // Create message record
        $whatsappMessage = WhatsAppMessage::create([
            'conversation_id' => $conversation->id,
            'whatsapp_message_id' => $message['id'],
            'direction' => 'inbound',
            'from_number' => $fromNumber,
            'to_number' => $metadata['display_phone_number'] ?? null,
            'message_type' => $messageType,
            'content' => $this->extractMessageContent($message),
            'status' => 'received',
        ]);

        // Update conversation
        $conversation->update([
            'last_message_at' => now(),
            'last_message_from' => 'customer',
        ]);

        // Process message intent (NLP / chatbot logic)
        $this->processMessageIntent($conversation, $whatsappMessage);

        Log::info('Inbound WhatsApp message processed', [
            'conversation_id' => $conversation->id,
            'message_id' => $whatsappMessage->id,
            'type' => $messageType,
        ]);
    }

    /**
     * Extract message content based on type
     */
    protected function extractMessageContent($message)
    {
        $type = $message['type'];

        switch ($type) {
            case 'text':
                return ['text' => $message['text']['body']];

            case 'image':
            case 'document':
            case 'video':
            case 'audio':
                return [
                    'mime_type' => $message[$type]['mime_type'] ?? null,
                    'id' => $message[$type]['id'] ?? null,
                    'caption' => $message[$type]['caption'] ?? null,
                ];

            case 'location':
                return [
                    'latitude' => $message['location']['latitude'] ?? null,
                    'longitude' => $message['location']['longitude'] ?? null,
                    'name' => $message['location']['name'] ?? null,
                ];

            case 'interactive':
                return [
                    'type' => $message['interactive']['type'] ?? null,
                    'button_reply' => $message['interactive']['button_reply'] ?? null,
                    'list_reply' => $message['interactive']['list_reply'] ?? null,
                ];

            default:
                return $message;
        }
    }

    /**
     * Find user by phone number
     */
    protected function findUserByPhone($phone)
    {
        // Clean phone number (remove + and whitespace)
        $cleanPhone = preg_replace('/[^0-9]/', '', $phone);

        $user = User::where('phone', 'LIKE', "%{$cleanPhone}%")->first();

        return $user ? $user->id : null;
    }

    /**
     * Process message intent (chatbot logic - to be implemented in Phase 3)
     */
    protected function processMessageIntent($conversation, $message)
    {
        // TODO: Implement NLP and chatbot logic
        // For now, just send a basic auto-reply

        $text = $message->getText();

        // Basic keyword detection (placeholder for Phase 3)
        if (stripos($text, 'bonjour') !== false || stripos($text, 'salut') !== false) {
            $this->sendReply($conversation, "Bonjour! Bienvenue chez ichri.tn 🛒\nComment puis-je vous aider aujourd'hui?");
        } elseif (stripos($text, 'prix') !== false || stripos($text, 'produit') !== false) {
            $this->sendReply($conversation, "Pour consulter nos produits et prix, tapez 'catalogue' ou visitez notre app mobile.");
        } elseif (stripos($text, 'commande') !== false || stripos($text, 'commander') !== false) {
            $this->sendReply($conversation, "Pour passer une commande, tapez 'menu' pour voir les options disponibles.");
        }

        // Note: Full chatbot implementation will be added in Phase 3.0
    }

    /**
     * Send reply message (placeholder - implement in Phase 3)
     */
    protected function sendReply($conversation, $text)
    {
        // TODO: Implement WhatsApp Business API message sending
        // For now, just log what would be sent

        Log::info('WhatsApp reply queued', [
            'conversation_id' => $conversation->id,
            'text' => $text,
        ]);

        // In Phase 3, this will call WhatsApp Business API to send message
        // and create a WhatsAppMessage record with direction='outbound'
    }

    /**
     * Update message delivery status
     */
    protected function updateMessageStatus($status)
    {
        $message = WhatsAppMessage::where('whatsapp_message_id', $status['id'])->first();

        if (!$message) {
            return;
        }

        switch ($status['status']) {
            case 'sent':
                $message->markAsSent();
                break;
            case 'delivered':
                $message->markAsDelivered();
                break;
            case 'read':
                $message->markAsRead();
                break;
            case 'failed':
                $message->markAsFailed($status['errors'][0]['title'] ?? 'Unknown error');
                break;
        }

        Log::info('WhatsApp message status updated', [
            'message_id' => $message->id,
            'status' => $status['status'],
        ]);
    }
}
