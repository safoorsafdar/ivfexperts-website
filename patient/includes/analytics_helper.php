<?php
/**
 * Treatment Analytics Helper
 * Provides parsed trends for patient dashboard (Follicles, Beta-hCG, etc.)
 */

class TreatmentAnalytics
{
    private $conn;

    public function __construct($conn)
    {
        $this->conn = $conn;
    }

    /**
     * Get follicle sizes over time from ultrasounds
     * @param int $patient_id
     * @return array
     */
    public function getFollicleTrends($patient_id)
    {
        // TODO: In a future iteration, parse the actual ultrasound report text 
        // to extract follicle sizes via Regex, or add structured columns to DB.
        // Returning empty array so UI gracefully shows "No data available yet" instead of crashing.
        return [];
    }

    /**
     * Get hormone levels over time from lab results
     * @param int $patient_id
     * @param string $hormone_name (e.g., 'hCG')
     * @return array
     */
    public function getHormoneTrends($patient_id, $hormone_name)
    {
        // TODO: Map to actual lab test IDs or names to extract numeric trends
        // Returns empty array for now.
        return [];
    }
}
