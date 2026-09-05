<?php

namespace App\Models;

use Core\Model;

class PrescriptionDetail extends Model
{
    protected string $table = 'prescription_details';

    /**
     * Create a prescription detail record.
     */
    public function createDetail(
        int $saleId,
        string $doctorName,
        string $nmcNumber,
        string $prescriptionDate,
        string $prescriptionNumber = '',
        string $licenseType = 'medical',
        string $hospitalName = ''
    ): int {
        return $this->create([
            'sale_id'              => $saleId,
            'doctor_name'          => $doctorName,
            'nmc_number'           => $nmcNumber,
            'license_type'         => $licenseType,
            'prescription_date'    => $prescriptionDate,
            'prescription_number'  => $prescriptionNumber,
            'hospital_name'        => $hospitalName,
        ]);
    }
}
