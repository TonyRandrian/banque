<?php

require_once __DIR__ . '/../vendor/fpdf/fpdf.php';

ini_set('display_errors', 1);
error_reporting(E_ALL);

class PretPDF
{
    /**
     * Génère un PDF pour un prêt donné avec ses paiements modalité
     * @param array $pret Détail du prêt (doit contenir les champs principaux)
     * @param array $paiements Liste des paiements modalité (tableau associatif)
     * @return string Données binaires du PDF
     */
    public static function genererPDF($pret, $paiements): string
    {
        // Fonction helper pour gérer les caractères spéciaux
        $convertText = function($text) {
            return iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $text);
        };

        // Création du PDF
        $pdf = new \FPDF();
        $pdf->AddPage();
        
        // En-tête avec titre principal
        $pdf->SetFillColor(230, 230, 230);
        $pdf->Rect(10, 10, 190, 20, 'F');
        $pdf->SetFont('Arial', 'B', 18);
        $pdf->SetXY(10, 15);
        $pdf->Cell(190, 10, $convertText('DÉTAIL DU PRÊT'), 0, 1, 'C');
        $pdf->Ln(10);

        // Informations du client
        $pdf->SetFont('Arial', 'B', 14);
        $pdf->SetFillColor(245, 245, 245);
        $pdf->Cell(190, 8, $convertText('Informations Client'), 0, 1, 'L', true);
        $pdf->Ln(2);
        
        $pdf->SetFont('Arial', '', 11);
        if (isset($pret['client_nom']) && isset($pret['client_prenom'])) {
            $pdf->Cell(70, 7, $convertText('Client :'), 0, 0, 'L');
            $pdf->SetFont('Arial', 'B', 11);
            $pdf->Cell(120, 7, $convertText($pret['client_prenom'] . ' ' . $pret['client_nom']), 0, 1, 'L');
            $pdf->SetFont('Arial', '', 11);
        }

        if (isset($pret['compte_numero'])) {
            $pdf->Cell(70, 7, $convertText('Numéro de compte :'), 0, 0, 'L');
            $pdf->SetFont('Arial', 'B', 11);
            $pdf->Cell(120, 7, $convertText($pret['compte_numero']), 0, 1, 'L');
            $pdf->SetFont('Arial', '', 11);
        }
        $pdf->Ln(5);

        // Détails du prêt
        $pdf->SetFont('Arial', 'B', 14);
        $pdf->SetFillColor(245, 245, 245);
        $pdf->Cell(190, 8, $convertText('Détails du Prêt'), 0, 1, 'L', true);
        $pdf->Ln(2);
        
        $pdf->SetFont('Arial', '', 11);
        $pdf->Cell(70, 7, $convertText('Montant emprunté :'), 0, 0, 'L');
        $pdf->SetFont('Arial', 'B', 11);
        $pdf->Cell(120, 7, number_format($pret['montant'], 2, ',', ' ') . ' ' . $convertText('€'), 0, 1, 'L');
        
        $pdf->SetFont('Arial', '', 11);
        $pdf->Cell(70, 7, $convertText('Durée de remboursement :'), 0, 0, 'L');
        $pdf->SetFont('Arial', 'B', 11);
        $pdf->Cell(120, 7, $pret['duree_remboursement'] . ' mois', 0, 1, 'L');
        
        $pdf->SetFont('Arial', '', 11);
        $pdf->Cell(70, 7, $convertText('Date de demande :'), 0, 0, 'L');
        $pdf->SetFont('Arial', 'B', 11);
        $pdf->Cell(120, 7, date('d/m/Y', strtotime($pret['date_demande'])), 0, 1, 'L');
        
        if (isset($pret['date_status'])) {
            $pdf->SetFont('Arial', '', 11);
            $pdf->Cell(70, 7, $convertText('Date de validation :'), 0, 0, 'L');
            $pdf->SetFont('Arial', 'B', 11);
            $pdf->Cell(120, 7, date('d/m/Y', strtotime($pret['date_status'])), 0, 1, 'L');
        }
        
        if (isset($pret['modalite_libelle'])) {
            $pdf->SetFont('Arial', '', 11);
            $pdf->Cell(70, 7, $convertText('Modalité :'), 0, 0, 'L');
            $pdf->SetFont('Arial', 'B', 11);
            $pdf->Cell(120, 7, $convertText($pret['modalite_libelle']), 0, 1, 'L');
        }
        
        if (isset($pret['type_pret_libelle'])) {
            $pdf->SetFont('Arial', '', 11);
            $pdf->Cell(70, 7, $convertText('Type de prêt :'), 0, 0, 'L');
            $pdf->SetFont('Arial', 'B', 11);
            $pdf->Cell(120, 7, $convertText($pret['type_pret_libelle']), 0, 1, 'L');
        }
        
        if (isset($pret['taux_assurance']) && $pret['taux_assurance'] > 0) {
            $pdf->SetFont('Arial', '', 11);
            $pdf->Cell(70, 7, $convertText('Taux assurance :'), 0, 0, 'L');
            $pdf->SetFont('Arial', 'B', 11);
            $pdf->Cell(120, 7, number_format($pret['taux_assurance'], 2) . ' %', 0, 1, 'L');
        }
        
        if (isset($pret['assurance_par_mois'])) {
            $pdf->SetFont('Arial', '', 11);
            $pdf->Cell(70, 7, $convertText('Assurance par mois :'), 0, 0, 'L');
            $pdf->SetFont('Arial', 'B', 11);
            $pdf->Cell(120, 7, $pret['assurance_par_mois'] ? 'Oui' : 'Non', 0, 1, 'L');
        }
        $pdf->Ln(10);

        // Tableau des paiements modalité
        $pdf->SetFont('Arial', 'B', 16);
        $pdf->SetFillColor(230, 230, 230);
        $pdf->Rect(10, $pdf->GetY(), 190, 12, 'F');
        $pdf->SetXY(10, $pdf->GetY() + 2);
        $pdf->Cell(190, 8, $convertText('ÉCHÉANCIER DES PAIEMENTS'), 0, 1, 'C');
        $pdf->Ln(5);
        
        // En-tête du tableau
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->SetFillColor(200, 200, 200);
        $pdf->Cell(25, 10, $convertText('N°'), 1, 0, 'C', true);
        $pdf->Cell(40, 10, $convertText('Date'), 1, 0, 'C', true);
        $pdf->Cell(40, 10, $convertText('Mensualité'), 1, 0, 'C', true);
        $pdf->Cell(40, 10, $convertText('Intérêts'), 1, 0, 'C', true);
        $pdf->Cell(45, 10, $convertText('Reste à payer'), 1, 1, 'C', true);
        
        $pdf->SetFont('Arial', '', 9);
        $totalMensualite = 0;
        $totalInterets = 0;
        $totalReste = 0;

        foreach ($paiements as $i => $paiement) {
            $date = is_array($paiement) ? ($paiement['date_prevu_paiment'] ?? '') : ($paiement->date_prevu_paiment ?? '');
            $mensualite = is_array($paiement) ? ($paiement['mensualite'] ?? $paiement['montant_prevu'] ?? 0) : ($paiement->mensualite ?? $paiement->montant_prevu ?? 0);
            $interets = is_array($paiement) ? ($paiement['interet'] ?? 0) : ($paiement->interet ?? 0);
            $reste = is_array($paiement) ? ($paiement['montant_restant'] ?? 0) : ($paiement->montant_restant ?? 0);

            // Alternance de couleurs pour les lignes
            $fillColor = ($i % 2 == 0) ? true : false;
            if ($fillColor) $pdf->SetFillColor(245, 245, 245);

            $pdf->Cell(25, 8, $i + 1, 1, 0, 'C', $fillColor);
            $pdf->Cell(40, 8, $date ? date('d/m/Y', strtotime($date)) : '', 1, 0, 'C', $fillColor);
            $pdf->Cell(40, 8, number_format($mensualite, 2, ',', ' ') . ' ' . $convertText('€'), 1, 0, 'R', $fillColor);
            $pdf->Cell(40, 8, number_format($interets, 2, ',', ' ') . ' ' . $convertText('€'), 1, 0, 'R', $fillColor);
            $pdf->Cell(45, 8, number_format($reste, 2, ',', ' ') . ' ' . $convertText('€'), 1, 1, 'R', $fillColor);
            
            $totalMensualite += $mensualite;
            $totalInterets += $interets;
            if ($i == 0) $totalReste = $reste; // Premier reste à payer seulement
        }
        
        // Ligne de totaux
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->SetFillColor(220, 220, 220);
        $pdf->Cell(25, 10, $convertText('TOTAL'), 1, 0, 'C', true);
        $pdf->Cell(40, 10, '', 1, 0, 'C', true);
        $pdf->Cell(40, 10, number_format($totalMensualite, 2, ',', ' ') . ' ' . $convertText('€'), 1, 0, 'R', true);
        $pdf->Cell(40, 10, number_format($totalInterets, 2, ',', ' ') . ' ' . $convertText('€'), 1, 0, 'R', true);
        $pdf->Cell(45, 10, number_format($totalReste, 2, ',', ' ') . ' ' . $convertText('€'), 1, 1, 'R', true);

        // Pied de page avec informations complémentaires
        $pdf->Ln(10);
        $pdf->SetFont('Arial', 'I', 8);
        $pdf->Cell(0, 5, $convertText('Document généré le ') . date('d/m/Y à H:i'), 0, 1, 'R');
        $pdf->Cell(0, 5, $convertText('Banque - Service des Prêts'), 0, 1, 'R');

        // Retourne le PDF en binaire
        return $pdf->Output('S'); // S = retourne le PDF sous forme de string
    }
}
