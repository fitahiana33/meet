<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Declarant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class DeclarantController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $term = trim((string) $request->query('search', ''));

        if (mb_strlen($term) < 2) {
            return response()->json(['data' => []]);
        }

        $like = '%'.$term.'%';
        $declarants = Declarant::query()
            ->where(fn ($query) => $query
                ->where('first_name', 'like', $like)
                ->orWhere('last_name', 'like', $like)
                ->orWhere('email', 'like', $like))
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->limit(12)
            ->get()
            ->map(fn (Declarant $declarant): array => $this->serialize($declarant));

        return response()->json(['data' => $declarants]);
    }

    public function photo(Declarant $declarant): BinaryFileResponse
    {
        abort_unless($declarant->photo_path && Storage::disk('local')->exists($declarant->photo_path), 404);

        return response()->file(Storage::disk('local')->path($declarant->photo_path));
    }

    private function serialize(Declarant $declarant): array
    {
        return [
            'id' => $declarant->id,
            'first_name' => $declarant->first_name,
            'last_name' => $declarant->last_name,
            'name' => $declarant->displayName(),
            'email' => $declarant->email,
            'address' => $declarant->address,
            'phone' => $declarant->phone,
            'has_photo' => (bool) $declarant->photo_path,
            'photo_url' => $declarant->photo_path ? '/declarants/'.$declarant->id.'/photo' : null,
        ];
    }
}
