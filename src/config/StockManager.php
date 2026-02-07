<?php
/**
 * classe para gerenciar estoque por tamanho
 */
class StockManager {
    
    /**
     * Obtém o estoque de um tamanho específico
     * Aceita tanto JSON string quanto array
     */
    public static function getStockBySize($stock_data, $size) {
        if (!$stock_data) {
            return 0;
        }
        
        // Se for string, decodifica; se for array, usa diretamente
        $stock = is_string($stock_data) ? json_decode($stock_data, true) ?? [] : $stock_data;
        return intval($stock[$size] ?? 0);
    }

    /**
     * Obtém o estoque total
     * Aceita tanto JSON string quanto array
     */
    public static function getTotalStock($stock_data) {
        if (!$stock_data) {
            return 0;
        }
        
        // Se for string, decodifica; se for array, usa diretamente
        $stock = is_string($stock_data) ? json_decode($stock_data, true) ?? [] : $stock_data;
        return array_sum($stock);
    }

    /**
     * Gera array de estoque a partir de post
     */
    public static function generateStockFromPost($sizes) {
        $stock_array = [];
        $tamanhos_disponiveis = ['PP', 'P', 'M', 'G', 'GG', 'XG', 'XGG'];
        
        foreach ($tamanhos_disponiveis as $tam) {
            $stock_key = "stock_$tam";
            $stock_array[$tam] = filter_var($_POST[$stock_key] ?? 0, FILTER_VALIDATE_INT) ?? 0;
            
            // Garante que não seja negativo
            if ($stock_array[$tam] < 0) {
                $stock_array[$tam] = 0;
            }
        }
        
        return $stock_array;
    }

    /**
     * Converte array de estoque para JSON
     */
    public static function arrayToJson($stock_array) {
        return json_encode($stock_array);
    }

    /**
     * Obtém array de estoque a partir de JSON
     */
    public static function jsonToArray($stock_json) {
        if (!$stock_json) {
            return [];
        }
        return json_decode($stock_json, true) ?? [];
    }

    /**
     * Formata estoque para exibição
     */
    public static function formatForDisplay($stock_json, $sizes_array) {
        $stock = self::jsonToArray($stock_json);
        $result = [];
        
        foreach ($sizes_array as $size) {
            $qty = $stock[$size] ?? 0;
            $result[] = "{$size}: {$qty}";
        }
        
        return implode(' | ', $result);
    }

    /**
     * Valida se há estoque disponível
     */
    public static function hasStock($stock_json) {
        return self::getTotalStock($stock_json) > 0;
    }

    /**
     * Valida se há estoque para um tamanho específico
     */
    public static function hasStockForSize($stock_json, $size) {
        return self::getStockBySize($stock_json, $size) > 0;
    }
}
