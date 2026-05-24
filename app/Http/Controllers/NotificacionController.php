<?php

namespace App\Http\Controllers;

use App\Models\Notificacion;
use Illuminate\Http\Request;

class NotificacionController extends Controller
{
    public function MisNotificaciones(Request $request)
    {
        return response()->json(
            Notificacion::where('id_users', $request->user()->id_users)
                ->orderBy('created_at', 'desc')
                ->paginate(15)
        );
    }

    public function ContadorNoLeidas(Request $request)
    {
        $count = Notificacion::where('id_users', $request->user()->id_users)
            ->where('leida', false)
            ->count();

        return response()->json(['no_leidas' => $count]);
    }

    public function MarcarLeida(Request $request, $id)
    {
        $notif = Notificacion::where('id', $id)
            ->where('id_users', $request->user()->id_users)
            ->firstOrFail();

        $notif->update(['leida' => true]);

        return response()->json(['message' => 'Notificación marcada como leída']);
    }

    public function MarcarTodasLeidas(Request $request)
    {
        Notificacion::where('id_users', $request->user()->id_users)
            ->where('leida', false)
            ->update(['leida' => true]);

        return response()->json(['message' => 'Todas las notificaciones marcadas como leídas']);
    }
}
