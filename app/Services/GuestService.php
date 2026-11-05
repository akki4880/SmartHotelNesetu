<?php

namespace App\Services;

use App\Models\Guest;
use App\Models\GuestIdProof;
use Illuminate\Support\Facades\Auth;

class GuestService
{
        public static function createOrUpdateGuest($guestData, $hotelId = null)
        {
            $hotelId = $hotelId ?? (Auth::check() ? Auth::user()->hotel_id : null);

            if (!$hotelId) {
                throw new \Exception('Hotel ID is required for guest creation');
            }

            // ✅ Check existing guest by mobile + hotel
            $guest = Guest::where('hotel_id', $hotelId)
                ->where('mobile', $guestData['mobile'])
                ->first();

            if (!$guest) {

                // ✅ CREATE
                $guest = Guest::create([
                    'hotel_id' => $hotelId,
                    'full_name' => $guestData['name'],
                    'mobile' => $guestData['mobile'],
                    'email' => $guestData['email'] ?? null,
                    'address' => $guestData['address'] ?? null,
                    'dob' => $guestData['dob'] ?? null,
                    'country_id' => $guestData['country_id'] ?? null,
                    'state_id' => $guestData['state_id'] ?? null,
                ]);
            } else {

                // ✅ UPDATE EXISTING
                $guest->update([
                    'full_name' => $guestData['name'],
                    'email' => $guestData['email'] ?? $guest->email,
                    'address' => $guestData['address'] ?? $guest->address,
                    'dob' => $guestData['dob'] ?? null,
                    'country_id' => $guestData['country_id'] ?? null,
                    'state_id' => $guestData['state_id'] ?? null,
                ]);
            }

            // ✅ SAVE ID PROOFS
            if (array_key_exists('id_proofs', $guestData)) {
                $deletedProofIds = array_map('intval', $guestData['deleted_proof_ids'] ?? []);
                $deletedProofIds = array_values(array_unique(array_filter($deletedProofIds)));

                if (!empty($deletedProofIds)) {
                    $guest->idProofs()->whereIn('id', $deletedProofIds)->get()->each(function ($proof) {
                        if ($proof->id_file_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($proof->id_file_path)) {
                            \Illuminate\Support\Facades\Storage::disk('public')->delete($proof->id_file_path);
                        }

                        $proof->delete();
                    });
                }
            }

            if (!empty($guestData['id_proofs'])) {
                foreach ($guestData['id_proofs'] as $proof) {

                    if (!isset($proof['id_type'])) {
                        continue;
                    }

                    $proofId = isset($proof['proof_id']) ? (int) $proof['proof_id'] : null;
                    if ($proofId) {
                        $existingProof = $guest->idProofs()->find($proofId);
                        if ($existingProof) {
                            if (!empty($proof['id_type'])) {
                                $existingProof->update([
                                    'id_type' => $proof['id_type'],
                                    'id_number' => $proof['id_number'] ?? $existingProof->id_number,
                                ]);
                            }
                            continue;
                        }
                    }

                    $existingProof = $guest->idProofs()
                        ->where('id_type', $proof['id_type'])
                        ->where('id_number', $proof['id_number'] ?? null)
                        ->first();

                    if ($existingProof) {
                        continue;
                    }

                    if (empty($proof['id_file']) && !($proof['id_file'] ?? null instanceof \Illuminate\Http\UploadedFile)) {
                        continue;
                    }

                    $path = null;

                    if (!empty($proof['id_file'])) {
                        $folder = "guest_ids/$hotelId/" . $guest->id;
                        $path = $proof['id_file']->store($folder, 'public');
                    }

                    GuestIdProof::create([
                        'guest_id' => $guest->id,
                        'id_type' => $proof['id_type'],
                        'id_number' => $proof['id_number'] ?? null,
                        'id_file_path' => $path,
                    ]);
                }
            }

            return $guest;
        }
}
