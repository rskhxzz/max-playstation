<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'full_name'         => 'required|string|max:150',
            'phone_number'      => 'required|string|max:20',
            'rental_date'       => 'required|date|after_or_equal:today',
            'rental_time'       => 'required|date_format:H:i',
            'rental_package_id' => 'required|string|exists:c_rental_package,id',
            'delivery_address'  => 'required|string',
            'google_place_id'   => 'nullable|string|max:255',
            'latitude'          => 'required|numeric|between:-90,90',
            'longitude'         => 'required|numeric|between:-180,180',
            'payment_option'    => 'required|in:full,deposit',
            'terms_agreed'      => 'required|accepted',
            'customer_notes'    => 'nullable|string|max:500',
            'booking_token' => 'required|uuid',
        ];
    }

    public function messages(): array
    {
        return [
            'full_name.required'         => 'Nama lengkap wajib diisi.',
            'phone_number.required'      => 'Nomor HP wajib diisi.',
            'rental_date.required'       => 'Tanggal sewa wajib dipilih.',
            'rental_date.after_or_equal' => 'Tanggal sewa tidak boleh di masa lampau.',
            'rental_time.required'       => 'Jam mulai sewa wajib dipilih.',
            'rental_package_id.required' => 'Paket sewa wajib dipilih.',
            'rental_package_id.exists'   => 'Paket sewa tidak valid.',
            'delivery_address.required'  => 'Alamat pengiriman wajib diisi.',
            'latitude.required'          => 'Silakan pilih lokasi pada peta.',
            'longitude.required'         => 'Silakan pilih lokasi pada peta.',
            'payment_option.required'    => 'Pilihan pembayaran wajib dipilih.',
            'payment_option.in'          => 'Pilihan pembayaran tidak valid.',
            'terms_agreed.required'      => 'Anda harus menyetujui syarat dan ketentuan.',
            'terms_agreed.accepted'      => 'Anda harus menyetujui syarat dan ketentuan.',
        ];
    }
}
