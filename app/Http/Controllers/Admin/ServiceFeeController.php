<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminLog;
use App\Models\ServiceFeeConfiguration;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ServiceFeeController extends Controller
{
    public function edit()
    {
        $active = ServiceFeeConfiguration::active();
        $history = ServiceFeeConfiguration::latest()->take(10)->get();

        return view('admin.service-fee.edit', compact('active', 'history'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'type' => ['required', Rule::in(['nominal', 'percentage'])],
            'value' => ['required', 'numeric', 'min:0'],
        ], [
            'type.required' => 'Pilih jenis biaya layanan.',
            'value.required' => 'Nilai biaya wajib diisi.',
            'value.numeric' => 'Nilai biaya harus berupa angka.',
            'value.min' => 'Nilai biaya tidak boleh negatif.',
        ]);

        if ($data['type'] === 'percentage' && $data['value'] > 100) {
            return back()->withErrors(['value' => 'Persentase maksimal 100.'])->withInput();
        }

        ServiceFeeConfiguration::where('is_active', true)->update(['is_active' => false]);

        $config = ServiceFeeConfiguration::create([
            'type' => $data['type'],
            'value' => $data['value'],
            'is_active' => true,
        ]);

        AdminLog::record('update_service_fee', 'service_fee', $config->id, "{$data['type']}: {$data['value']}");

        return back()->with('status', 'Biaya layanan berhasil diperbarui.');
    }
}