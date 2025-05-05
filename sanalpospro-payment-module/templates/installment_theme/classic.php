<?php
if (!defined('ABSPATH')) {
    exit;
}

wp_enqueue_style(
    'sanalpospro-installment-classic',
    SPPRO_PLUGIN_URL . 'assets/css/installment-classic.css',
    array(),
    SPPRO_VERSION
);

global $product;
if (!$product) return;

$price = $product->get_price();
$installments = json_decode(empty($installments) ? '[]' : $installments, true);
$all_card_families = [
    'world', 'axess', 'bonus', 'cardfinans', 'maximum',
    'paraf', 'saglamcard', 'advantage', 'combo', 'miles-smiles'
];

?>

<div data-sanalpospro-wrapper>
    <div data-sanalpospro-container>
        <?php foreach($all_card_families as $card_key) : 
            $has_any_installment = false;
            
           
            if(!empty($installments[$card_key])) {
                $card_installments = array_filter($installments[$card_key], function($installment) {
                    return $installment['gateway'] !== 'off';
                });

                if(!empty($card_installments)) {
                    $has_any_installment = true;
                }
            }

            if(!$has_any_installment) continue;

            echo '<div data-sanalpospro-card>';
            echo '<table data-sanalpospro-table>';
            echo '<thead>';
            echo '<tr>';
            echo '<td colspan="3">';
            echo wp_kses_post(sppro_get_card_image($card_key));
            echo '</td>';
            echo '</tr>';
            echo '<tr>';
            echo '<td width="33.33%">' . esc_html__('Installment', 'sanalpospro-payment-module') . '</td>';
            echo '<td width="33.33%">' . esc_html__('Monthly Payment', 'sanalpospro-payment-module') . '</td>';
            echo '<td width="33.33%">' . esc_html__('Total', 'sanalpospro-payment-module') . '</td>';
            echo '</tr>';
            echo '</thead>';
            echo '<tbody>';

            for($i = 1; $i <= 12; $i++) {
                $installment_exists = false;
                $monthly_payment = '-';
                $total_amount = '-';

                foreach($card_installments as $installment) {
                    if($installment['months'] == $i) {
                        $installment_exists = true;
                        if($i == 1 && $installment['gateway_fee_percent'] == 0) {
                            $total = $price + (($price * $installment['gateway_fee_percent'])/100);
                            $monthly = $total;
                        } else {
                            $total = $price * (1 + $installment['gateway_fee_percent']/100);
                            $monthly = $total/$i;
                        }
                        $monthly_payment = wp_kses_post(wc_price($monthly));
                        $total_amount = wp_kses_post(wc_price($total));
                        break;
                    }
                }

                echo '<tr>';
                echo '<td>' . ($i == 1 ? 
                     esc_html__('Cash', 'sanalpospro-payment-module') : 
                     sprintf(
                         /* translators: %d: Installment number */
                         esc_html__('%d. Installment', 'sanalpospro-payment-module'),
                         esc_html($i)
                     )) . '</td>';
                echo '<td>' . wp_kses_post($monthly_payment) . '</td>';
                echo '<td>' . wp_kses_post($total_amount) . '</td>';
                echo '</tr>';
            }

            echo '</tbody>';
            echo '</table>';
            echo '</div>';
        endforeach; ?>
    </div>

    <div data-sanalpospro-note>
        <p><?php esc_html_e('* Installment amounts are estimated and may vary according to your bank\'s campaigns and interest rates.', 'sanalpospro-payment-module'); ?></p>
    </div>
</div>