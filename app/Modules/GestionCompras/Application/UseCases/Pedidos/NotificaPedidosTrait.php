<?php

namespace App\Modules\GestionCompras\Application\UseCases\Pedidos;

use App\Models\CpPedido;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Mail\GerenciaApprovedNotification;
use App\Mail\GerenciaRejectedNotification;
use App\Mail\OrderApprovedNotification;
use App\Mail\OrderRejectedNotification;
use App\Mail\NewOrderNotification;

trait NotificaPedidosTrait
{
    protected function sendGerenciaApprovedNotification(CpPedido $pedido)
    {
        try {
            $user = $pedido->creador ?? $pedido->elaboradoPor;
            if ($user && $user->email) {
                Mail::to($user->email)->send(new GerenciaApprovedNotification($pedido));
            }
        } catch (\Exception $e) {
            Log::error("Error sending GerenciaApprovedNotification: " . $e->getMessage());
        }
    }

    protected function sendGerenciaRejectedNotification(CpPedido $pedido, $motivo)
    {
        try {
            $user = $pedido->creador ?? $pedido->elaboradoPor;
            if ($user && $user->email) {
                Mail::to($user->email)->send(new GerenciaRejectedNotification($pedido, $motivo));
            }
        } catch (\Exception $e) {
            Log::error("Error sending GerenciaRejectedNotification: " . $e->getMessage());
        }
    }

    protected function sendOrderApprovedNotification(CpPedido $pedido)
    {
        try {
            $user = $pedido->creador ?? $pedido->elaboradoPor;
            if ($user && $user->email) {
                Mail::to($user->email)->send(new OrderApprovedNotification($pedido));
            }
        } catch (\Exception $e) {
            Log::error("Error sending OrderApprovedNotification: " . $e->getMessage());
        }
    }

    protected function sendOrderRejectedNotification(CpPedido $pedido, $motivo)
    {
        try {
            $user = $pedido->creador ?? $pedido->elaboradoPor;
            if ($user && $user->email) {
                Mail::to($user->email)->send(new OrderRejectedNotification($pedido, $motivo));
            }
        } catch (\Exception $e) {
            Log::error("Error sending OrderRejectedNotification: " . $e->getMessage());
        }
    }

    protected function sendNewOrderNotification(CpPedido $pedido)
    {
        try {
            // For new orders, we notify someone who needs to review it or we can just log it for now
            // If there's a specific role or email, it goes here. For now, we will send to a generic address
            // or just log if no generic address is found.
            $adminEmail = config('mail.admin_address', 'admin@nexacore.local');
            Mail::to($adminEmail)->send(new NewOrderNotification($pedido));
        } catch (\Exception $e) {
            Log::error("Error sending NewOrderNotification: " . $e->getMessage());
        }
    }
}
