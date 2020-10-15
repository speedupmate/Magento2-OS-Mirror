<?php
/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 *
 * This script is used to generate the values for the VertexTestCaseValuesData.xml file.
 *
 * Since we're running tests based on changing tax calculations, they can be flaky.  This file allows us to
 * regenerate the numerical values whenever the tax rates change.
 */

/** Tax rate when a product is shipped from PA to PA without any city sales tax applied */
const TAX_RATE_PA_ONLY = 6;

/** Tax rate when a product is shipped from PA to Santa Monica, California */
const TAX_RATE_PA_TO_SANTAMONICA = 10.25;

/** Tax rate when a product is shipped to Valencia, Spain */
const TAX_RATE_VALENCIA = 21;

/** Tax rate when a product is shipped to Roma, Italy */
const TAX_RATE_ROMA = 22;

/** Tax rate when a product is shipped to Montreal, Canada */
const TAX_RATE_QUEBEC = 14.975;

const TAX_RATE_US_IL = 6.25;

/** Tax rate for California */
const TAX_RATE_US_CA = 9.5;

/** Tax rate for Danville, Illinois */
const TAX_RATE_US_IL_DANVILLE = 2.75;
const TAX_RATE_US_IL_VERMILION = 0.25;

const TAX_RATE_COMBINED_US_IL_DANVILLE = TAX_RATE_COMBINED_US_IL_DANVILLE;

/** Tax rate for River Grive, Illinois */
const TAX_RATE_US_IL_COOK = 1.75;
const TAX_RATE_US_IL_RIVER_GROVE = 2;
const TAX_RATE_US_IL_COOK_RTA = 1;
const TAX_RATE_COMBINED_US_IL_RIVER_GROVE = TAX_RATE_US_IL + TAX_RATE_US_IL_COOK + TAX_RATE_US_IL_RIVER_GROVE +
    TAX_RATE_US_IL_COOK_RTA;

$entities = [
    'Vertex_100USD_PA_Only_Values' => [
        'price' => 100,
        'priceInclTax' => 100 * (TAX_RATE_PA_ONLY / 100 + 1),
        'subtotal' => 100,
        'subtotalInclTax' => 100 * (TAX_RATE_PA_ONLY / 100 + 1),
        'tax' => 100 * TAX_RATE_PA_ONLY / 100,
        'taxPercent' => TAX_RATE_PA_ONLY,
    ],
    'Vertex_100USD_PA_To_DE_Values' => [
        'price' => 100,
        'priceInclTax' => 100,
        'subtotal' => 100,
        'subtotalInclTax' => 100,
        'tax' => 0,
        'taxPercent' => 0,
    ],
    'Vertex_34USD_PA_Only_Values' => [
        'price' => 34.00,
        'priceInclTax' => 34 * (TAX_RATE_PA_ONLY / 100 + 1),
        'subtotal' => 34.00,
        'subtotalInclTax' => 34 * (TAX_RATE_PA_ONLY / 100 + 1),
        'tax' => 34 * (TAX_RATE_PA_ONLY / 100),
        'taxPercent' => TAX_RATE_PA_ONLY,
    ],
    'Vertex_34USD_PA_To_DE_Values' => [
        'price' => 34.00,
        'priceInclTax' => 34,
        'subtotal' => 34,
        'subtotalInclTax' => 34,
        'tax' => 0,
        'taxPercent' => 0,
    ],
    'Vertex_18USD_PA_Only_Values' => [
        'price' => 18.00,
        'priceInclTax' => 18 * (TAX_RATE_PA_ONLY / 100 + 1),
        'subtotal' => 18.00,
        'subtotalInclTax' => 18 * (TAX_RATE_PA_ONLY / 100 + 1),
        'tax' => 18 * TAX_RATE_PA_ONLY / 100,
        'taxPercent' => TAX_RATE_PA_ONLY,
    ],
    'Vertex_100USD_Clothing_PA_Only_Values' => [
        // Clothing is not taxed in PA
        'price' => 100,
        'priceInclTax' => 100,
        'subtotal' => 100,
        'subtotalInclTax' => 100,
        'tax' => 0,
        'taxPercent' => 0,
    ],
    'Vertex_100USD_SantaMonica_Values' => [
        'price' => 100,
        'priceInclTax' => 100 * (TAX_RATE_PA_TO_SANTAMONICA / 100 + 1),
        'subtotal' => 100,
        'subtotalInclTax' => 100 * (TAX_RATE_PA_TO_SANTAMONICA / 100 + 1),
        'tax' => 100 * TAX_RATE_PA_TO_SANTAMONICA / 100,
        'taxPercent' => TAX_RATE_PA_TO_SANTAMONICA,
    ],
    'Vertex_18USD_SantaMonica_Values' => [
        'price' => 18.00,
        'priceInclTax' => 18 * (TAX_RATE_PA_TO_SANTAMONICA / 100 + 1),
        'subtotal' => 18.00,
        'subtotalInclTax' => 18 * (TAX_RATE_PA_TO_SANTAMONICA / 100 + 1),
        'tax' => 18 * TAX_RATE_PA_TO_SANTAMONICA / 100,
        'taxPercent' => TAX_RATE_PA_TO_SANTAMONICA,
    ],
    'Vertex_100USD_Clothing_SantaMonica_Values' => [
        'price' => 100,
        'priceInclTax' => 100 * (TAX_RATE_PA_TO_SANTAMONICA / 100 + 1),
        'subtotal' => 100,
        'subtotalInclTax' => 100 * (TAX_RATE_PA_TO_SANTAMONICA / 100 + 1),
        'tax' => 100 * TAX_RATE_PA_TO_SANTAMONICA / 100,
        'taxPercent' => TAX_RATE_PA_TO_SANTAMONICA,
    ],
    'Vertex_Bundle_Valencia_Ball_Values' => [
        'price' => 23,
    ],
    'Vertex_Bundle_Valencia_Brick_Values' => [
        'price' => 5,
    ],
    'Vertex_Bundle_Valencia_Strap_Values' => [
        'price' => 14,
    ],
    'Vertex_Bundle_Valencia_Roller_Values' => [
        'price' => 19,
    ],
    'Vertex_Bundle_Valencia_Values' => [
        'price' => 61,
        'priceInclTax' => 61 * (TAX_RATE_VALENCIA / 100 + 1),
        'subtotal' => 61,
        'subtotalInclTax' => 61 * (TAX_RATE_VALENCIA / 100 + 1),
        'tax' => 61 * TAX_RATE_VALENCIA / 100,
        'taxPercent' => TAX_RATE_VALENCIA,
    ],
    'Vertex_Bundle_PA_Values' => [
        'price' => 61,
        'priceInclTax' => 61 * (TAX_RATE_PA_ONLY / 100 + 1),
        'subtotal' => 61,
        'subtotalInclTax' => 61 * (TAX_RATE_PA_ONLY / 100 + 1),
        'tax' => 61 * TAX_RATE_PA_ONLY / 100,
        'taxPercent' => TAX_RATE_PA_ONLY,
    ],
    'Vertex_Bundle_PA_Values_Qty3' => [
        'price' => 61,
        'priceInclTax' => 61 * (TAX_RATE_PA_ONLY / 100 + 1),
        'subtotal' => 61,
        'subtotalInclTax' => 61 * (TAX_RATE_PA_ONLY / 100 + 1),
        'rowTotal' => 61 * 3,
        'rowTotalInclTax' => 61 * 3 * (TAX_RATE_PA_ONLY / 100 + 1),
        'rowTax' => 61 * 3 * TAX_RATE_PA_ONLY / 100,
        'tax' => 61 * TAX_RATE_PA_ONLY / 100,
        'taxPercent' => TAX_RATE_PA_ONLY,
    ],
    'Vertex_Bundle_PA_Values_Brick_Qty3' => [
        'price' => 5,
        'priceInclTax' => 5 * (TAX_RATE_PA_ONLY / 100 + 1),
        'subtotal' => 5,
        'subtotalInclTax' => 5 * (TAX_RATE_PA_ONLY / 100 + 1),
        'rowTotal' => 5 * 3,
        'rowTotalInclTax' => 5 * 3 * (TAX_RATE_PA_ONLY / 100 + 1),
        'rowTax' => 5 * 3 * TAX_RATE_PA_ONLY / 100,
        'tax' => 5 * TAX_RATE_PA_ONLY / 100,
        'taxPercent' => TAX_RATE_PA_ONLY,
    ],
    'Vertex_Bundle_PA_Values_Strap_Qty3' => [
        'price' => 14,
        'priceInclTax' => 14 * (TAX_RATE_PA_ONLY / 100 + 1),
        'subtotal' => 14,
        'subtotalInclTax' => 14 * (TAX_RATE_PA_ONLY / 100 + 1),
        'rowTotal' => 14 * 3,
        'rowTotalInclTax' => 14 * 3 * (TAX_RATE_PA_ONLY / 100 + 1),
        'rowTax' => 14 * 3 * TAX_RATE_PA_ONLY / 100,
        'tax' => 14 * TAX_RATE_PA_ONLY / 100,
        'taxPercent' => TAX_RATE_PA_ONLY,
    ],
    'Vertex_Bundle_PA_Values_Roller_Qty3' => [
        'price' => 19,
        'priceInclTax' => 19 * (TAX_RATE_PA_ONLY / 100 + 1),
        'subtotal' => 19,
        'subtotalInclTax' => 19 * (TAX_RATE_PA_ONLY / 100 + 1),
        'rowTotal' => 19 * 3,
        'rowTotalInclTax' => 19 * 3 * (TAX_RATE_PA_ONLY / 100 + 1),
        'rowTax' => 19 * 3 * TAX_RATE_PA_ONLY / 100,
        'tax' => 19 * TAX_RATE_PA_ONLY / 100,
        'taxPercent' => TAX_RATE_PA_ONLY,
    ],
    'Vertex_Bundle_PA_Values_Ball_Qty3' => [
        'price' => 23,
        'priceInclTax' => 23 * (TAX_RATE_PA_ONLY / 100 + 1),
        'subtotal' => 23,
        'subtotalInclTax' => 23 * (TAX_RATE_PA_ONLY / 100 + 1),
        'rowTotal' => 23 * 3,
        'rowTotalInclTax' => 23 * 3 * (TAX_RATE_PA_ONLY / 100 + 1),
        'rowTax' => 23 * 3 * TAX_RATE_PA_ONLY / 100,
        'tax' => 23 * TAX_RATE_PA_ONLY / 100,
        'taxPercent' => TAX_RATE_PA_ONLY,
    ],
    'Vertex_Bundle_PA_Values_Ball_Qty1' => [
        'price' => 23,
        'priceInclTax' => 23 * (TAX_RATE_PA_ONLY / 100 + 1),
        'subtotal' => 23,
        'subtotalInclTax' => 23 * (TAX_RATE_PA_ONLY / 100 + 1),
        'tax' => 23 * TAX_RATE_PA_ONLY / 100,
        'taxPercent' => TAX_RATE_PA_ONLY,
    ],
    'Vertex_Bundle_PA_Values_Brick_Qty1' => [
        'price' => 5,
        'priceInclTax' => 5 * (TAX_RATE_PA_ONLY / 100 + 1),
        'subtotal' => 5,
        'subtotalInclTax' => 5 * (TAX_RATE_PA_ONLY / 100 + 1),
        'tax' => 5 * TAX_RATE_PA_ONLY / 100,
        'taxPercent' => TAX_RATE_PA_ONLY,
    ],
    'Vertex_Bundle_PA_Values_Qty1' => [
        'price' => 61,
        'priceInclTax' => 61 * (TAX_RATE_PA_ONLY / 100 + 1),
        'subtotal' => 61,
        'subtotalInclTax' => 61 * (TAX_RATE_PA_ONLY / 100 + 1),
        'tax' => 61 * TAX_RATE_PA_ONLY / 100,
        'taxPercent' => TAX_RATE_PA_ONLY,
    ],
    'Vertex_Bundle_Partial_PA_Values_Qty1' => [
        'price' => 42,
        'priceInclTax' => 42 * (TAX_RATE_PA_ONLY / 100 + 1),
        'subtotal' => 42,
        'subtotalInclTax' => 42 * (TAX_RATE_PA_ONLY / 100 + 1),
        'tax' => 42 * TAX_RATE_PA_ONLY / 100,
        'taxPercent' => TAX_RATE_PA_ONLY,
    ],
    'Vertex_Grouped_First_Values' => [
        'price' => 14,
        'priceInclTax' => 14 * (TAX_RATE_ROMA / 100 + 1),
        'subtotal' => 14,
        'subtotalInclTax' => 14 * (TAX_RATE_ROMA / 100 + 1),
        'tax' => 14 * TAX_RATE_ROMA / 100,
        'taxPercent' => TAX_RATE_ROMA
    ],
    'Vertex_Grouped_CAN_First_Values' => [
        'price' => 14,
        'priceInclTax' => 14 * (TAX_RATE_QUEBEC / 100 + 1),
        'subtotal' => 14,
        'subtotalInclTax' => 14 * (TAX_RATE_QUEBEC / 100 + 1),
        'tax' => 14 * TAX_RATE_QUEBEC / 100,
        'taxPercent' => TAX_RATE_QUEBEC
    ],
    'Vertex_Grouped_Second_Values' => [
        'price' => 17,
        'priceInclTax' => 17 * (TAX_RATE_ROMA / 100 + 1),
        'subtotal' => 17,
        'subtotalInclTax' => 17 * (TAX_RATE_ROMA / 100 + 1),
        'tax' => 17 * TAX_RATE_ROMA / 100,
        'taxPercent' => TAX_RATE_ROMA
    ],
    'Vertex_Grouped_CAN_Second_Values' => [
        'price' => 17,
        'priceInclTax' => floor(100 * 17 * (TAX_RATE_QUEBEC / 100 + 1)) / 100,
        'subtotal' => 17,
        'subtotalInclTax' => floor(100 * 17 * (TAX_RATE_QUEBEC / 100 + 1)) / 100,
        'tax' => floor(17 * TAX_RATE_QUEBEC) / 100,
        'taxPercent' => TAX_RATE_QUEBEC
    ],
    'Vertex_Grouped_Third_Values' => [
        'price' => 21,
        'priceInclTax' => 21 * (TAX_RATE_ROMA / 100 + 1),
        'subtotal' => 21,
        'subtotalInclTax' => 21 * (TAX_RATE_ROMA / 100 + 1),
        'tax' => 21 * TAX_RATE_ROMA / 100,
        'taxPercent' => TAX_RATE_ROMA
    ],
    'Vertex_Grouped_CAN_Third_Values' => [
        'price' => 21,
        'priceInclTax' => 21 * (TAX_RATE_QUEBEC / 100 + 1),
        'subtotal' => 21,
        'subtotalInclTax' => 21 * (TAX_RATE_QUEBEC / 100 + 1),
        'tax' => 21 * TAX_RATE_QUEBEC / 100,
        'taxPercent' => TAX_RATE_QUEBEC
    ],
    'Vertex_Grouped_Product_Values' => [
        'price' => 100,
    ],
    'Vertex_EcoProduct_Values' => [
        'price' => 100,
        'priceInclTax' => floor(100 * 100 * ((TAX_RATE_COMBINED_US_IL_DANVILLE) / 100 + 1)) / 100,
        'subtotal' => 100,
        'subtotalInclTax' => floor(100 * 100 * ((TAX_RATE_COMBINED_US_IL_DANVILLE) / 100 + 1)) / 100,
        'tax' => floor(100 * 100 * (TAX_RATE_COMBINED_US_IL_DANVILLE) / 100) / 100,
        'taxPercent' => TAX_RATE_COMBINED_US_IL_DANVILLE,
    ],
    'Vertex_ElectronicProduct_Values' => [
        'price' => 100,
        'priceInclTax' => floor(100 * 100 * ((TAX_RATE_COMBINED_US_IL_RIVER_GROVE) / 100 + 1)) / 100,
        'subtotal' => 100,
        'subtotalInclTax' => floor(100 * 100 * ((TAX_RATE_COMBINED_US_IL_RIVER_GROVE) / 100 + 1)) / 100,
        'tax' => floor(100 * 100 * (TAX_RATE_COMBINED_US_IL_RIVER_GROVE) / 100) / 100,
        'taxPercent' => TAX_RATE_COMBINED_US_IL_RIVER_GROVE,
    ],
    'Vertex_100USD_TaxRegistration_VRTXMFTF01_Values' => [
        'price' => 100,
        'priceInclTax' => 100 * 1.0887,
        'subtotal' => 100,
        'subtotalInclTax' => 100 * 1.0887,
        'tax' => 100 * 8.887 / 100,
        'taxPercent' => 8.887,
    ],
    'Vertex_100USD_TaxRegistration_VRTXMFTF02_Values' => [
        'price' => 100,
        'priceInclTax' => 100 * 1.0950,
        'subtotal' => 100,
        'subtotalInclTax' => 100 * 1.0950,
        'tax' => 100 * .095,
        'taxPercent' => 9.5
    ],
    'Vertex_19USD_TCSIX_Values' => [
        'price' => 20,
        'priceInclTax' => 20 * (TAX_RATE_US_CA / 100 + 1),
        'subtotal' => 20,
        'subtotalInclTax' => 20 * (TAX_RATE_US_CA / 100 + 1),
        'tax' => 20 * TAX_RATE_US_CA / 100,
        'taxPercent' => TAX_RATE_US_CA,
    ],
    'Vertex_USD_ZeroPrice_Values' => [
        'price' => 9.99,
        'custom_zero_price' => 0,
    ],
    'Vertex_Bundle_Fixed_With_Ball_TCSIX_Values' => [
        'basePrice' => 15,
        'ballPrice' => 4,
        'price' => 19,
        'priceInclTax' => 19 * (TAX_RATE_US_CA / 100 + 1),
        'subtotal' => 19,
        'subtotalInclTax' => 19 * (TAX_RATE_US_CA / 100 + 1),
        'tax' => 19 * TAX_RATE_US_CA / 100,
        'taxPercent' => TAX_RATE_US_CA,
    ],
];

$totals = [
    'Vertex_Bundle_PA_Ball_Brick_Qty3_Totals' => [
        'pieces' => [
            'Vertex_Bundle_PA_Values_Qty3',
            'Vertex_Bundle_PA_Values_Ball_Qty3',
            'Vertex_Bundle_PA_Values_Brick_Qty3',
        ],
        'shipping' => 15,
        'shippingTax' => 15 * TAX_RATE_PA_ONLY / 100,
        'shippingInclTax' => 15 * (TAX_RATE_PA_ONLY / 100 + 1),
    ],
    'Vertex_100USD_Virtual_and_Downloadable_PA_Only_Totals' => [
        'pieces' => [
            'Vertex_100USD_PA_Only_Values',
            'Vertex_100USD_PA_Only_Values',
        ],
        'shipping' => 0,
        'shippingTax' => 0,
        'shippingInclTax' => 0,
    ],
    'Vertex_100USD_Virtual_PA_Only_Totals' => [
        'pieces' => [
            'Vertex_100USD_PA_Only_Values',
        ],
        'shipping' => 0,
        'shippingTax' => 0,
        'shippingInclTax' => 0,
    ],
    'Vertex_100USD_PA_To_DE_Totals' => [
        'pieces' => [
            'Vertex_100USD_PA_To_DE_Values',
        ],
        'shipping' => 0,
        'shippingTax' => 0,
        'shippingInclTax' => 0,
    ],
    'Vertex_34USD_PA_Only_Totals' => [
        'pieces' => [
            'Vertex_34USD_PA_Only_Values',
        ],
        'shipping' => 5,
        'shippingTax' => 5 * TAX_RATE_PA_ONLY / 100,
        'shippingInclTax' => 5 * (TAX_RATE_PA_ONLY / 100 + 1),
    ],
    'Vertex_34USD_PA_To_DE_Totals' => [
        'pieces' => [
            'Vertex_34USD_PA_To_DE_Values',
        ],
        'shipping' => 5,
        'shippingTax' => 0,
        'shippingInclTax' => 5,
    ],
    'Vertex_100USD_and_18USD_and_100USD_Clothing_PA_Only_Totals' => [
        'pieces' => [
            'Vertex_100USD_PA_Only_Values',
            'Vertex_18USD_PA_Only_Values',
            'Vertex_100USD_Clothing_PA_Only_Values',
        ],
        'shipping' => 15,
        'shippingTax' => 15 * TAX_RATE_PA_ONLY / 100,
        'shippingInclTax' => 15 * (TAX_RATE_PA_ONLY / 100 + 1)
    ],
    'Vertex_100USD_and_18USD_and_100USD_Clothing_SantaMonica_Totals' => [
        'pieces' => [
            'Vertex_100USD_SantaMonica_Values',
            'Vertex_18USD_SantaMonica_Values',
            'Vertex_100USD_Clothing_SantaMonica_Values',
        ],
        'shipping' => 15,
        'shippingTax' => 15 * TAX_RATE_PA_TO_SANTAMONICA / 100,
        'shippingInclTax' => 15 * (TAX_RATE_PA_TO_SANTAMONICA / 100 + 1)
    ],
    'Vertex_Bundle_Valencia_Totals' => [
        'pieces' => [
            'Vertex_Bundle_Valencia_Values',
        ],
        'shipping' => 5,
        'shippingTax' => 5 * TAX_RATE_VALENCIA / 100,
        'shippingInclTax' => 5 * (TAX_RATE_VALENCIA / 100 + 1),
    ],
    'Vertex_Bundle_PA_Totals_Qty3' => [
        'pieces' => [
            'Vertex_Bundle_PA_Values_Qty3',
        ],
        'shipping' => 15,
        'shippingTax' => 15 * TAX_RATE_PA_ONLY / 100,
        'shippingInclTax' => 15 * (TAX_RATE_PA_ONLY / 100 + 1),
    ],
    'Vertex_Bundle_Brick_Strap_Ball_PA_Totals_Qty3_Qty1' => [
        'pieces' => [
            'Vertex_Bundle_Partial_PA_Values_Qty1',
        ],
        'shipping' => 15,
        'shippingTax' => 15 * TAX_RATE_PA_ONLY / 100,
        'shippingInclTax' => 15 * (TAX_RATE_PA_ONLY / 100 + 1),
    ],
    'Vertex_Bundle_PA_Totals_Qty1' => [
        'pieces' => [
            'Vertex_Bundle_PA_Values_Qty1',
        ],
        'shipping' => 15,
        'shippingTax' => 15 * TAX_RATE_PA_ONLY / 100,
        'shippingInclTax' => 15 * (TAX_RATE_PA_ONLY / 100 + 1),
    ],
    'Vertex_Grouped_Product_Totals' => [
        'pieces' => [
            'Vertex_Grouped_First_Values',
            'Vertex_Grouped_Second_Values',
            'Vertex_Grouped_Third_Values',
        ],
        'shipping' => 15,
        'shippingTax' => 15 * TAX_RATE_ROMA / 100,
        'shippingInclTax' => 15 * (TAX_RATE_ROMA / 100 + 1),
    ],
    'Vertex_Grouped_CAN_Product_Totals' => [
        'pieces' => [
            'Vertex_Grouped_CAN_First_Values',
            'Vertex_Grouped_CAN_Second_Values',
            'Vertex_Grouped_CAN_Third_Values',
        ],
        'shipping' => 15,
        'shippingTax' => 15 * TAX_RATE_QUEBEC / 100,
        'shippingInclTax' => 15 * (TAX_RATE_QUEBEC / 100 + 1),
        'subtotalInclTax' => 59.78,
    ],
    'Vertex_EcoProduct_Totals' => [
        'pieces' => [
            'Vertex_EcoProduct_Values',
        ],
        'shipping' => 5,
        'shippingTax' => 5 * TAX_RATE_US_IL / 100,
        'shippingInclTax' => 5 * TAX_RATE_US_IL / 100 + 1,
    ],
    'Vertex_ElectronicProduct_Totals' => [
        'pieces' => [
            'Vertex_ElectronicProduct_Values',
        ],
        'shipping' => 5,
        'shippingTax' => 5 * TAX_RATE_US_IL / 100,
        'shippingInclTax' => 5 * TAX_RATE_US_IL / 100 + 1,
    ],
    'Vertex_19USD_TCSIX_Totals' => [
        'taxRate' => TAX_RATE_US_CA,
        'pieces' => [
            'Vertex_19USD_TCSIX_Values',
        ],
        'shipping' => 5,
        'shippingTax' => ceil(100 * 5 * (TAX_RATE_US_CA / 100)) / 100,
        'shippingInclTax' => ceil(100 * 5 * (TAX_RATE_US_CA / 100 + 1)) / 100
    ],
    'Vertex_Bundle_Valencia_Ball_Brick_Qty3_Totals' => [
        'pieces' => [
            'Vertex_Bundle_Valencia_Values'
        ],
        'shipping' => 5,
        'shippingTax' => ceil(100 * 5 * (TAX_RATE_VALENCIA / 100)) / 100,
        'shippingInclTax' => ceil(100 * 5 * (TAX_RATE_VALENCIA / 100 + 1)) / 100
    ],
    'Vertex_Bundle_Fixed_With_Ball_TCSIX_Totals' => [
        'taxRate' => TAX_RATE_US_CA,
        'pieces' => [
            'Vertex_Bundle_Fixed_With_Ball_TCSIX_Values'
        ],
        'shipping' => 5,
        'shippingTax' => ceil(100 * 5 * (TAX_RATE_US_CA / 100)) / 100,
        'shippingInclTax' => ceil(100 * 5 * (TAX_RATE_US_CA / 100 + 1)) / 100,
    ],
    'Vertex_100USD_TaxRegistration_VRTXMFTF01_Totals' => [
        'taxRate' => 8.87,
        'pieces' => [
            'Vertex_100USD_TaxRegistration_VRTXMFTF01_Values'
        ],
        'shipping' => 5,
        'shippingTax' => ceil(100 * 5 * .0887) / 100,
        'shippingInclTax' => ceil(100 * 5 * 1.0887) / 100,
    ],
    'Vertex_100USD_TaxRegistration_VRTXMFTF02_Totals' => [
        'taxRate' => 9.50,
        'pieces' => [
            'Vertex_100USD_TaxRegistration_VRTXMFTF02_Values'
        ],
        'shipping' => 5,
        'shippingTax' => ceil(100 * 5 * .095) / 100,
        'shippingInclTax' => ceil(100 * 5 * 1.095) / 100,
    ]
];

$jurisdictions = [
    'Vertex_Jurisdiction_IL' => ['label' => 'ILLINOIS', 'percent' => TAX_RATE_US_IL],
    'Vertex_Jurisdiction_IL_Vermilion' => ['label' => 'VERMILION', 'percent' => TAX_RATE_US_IL_VERMILION],
    'Vertex_Jurisdiction_IL_Danville' => ['label' => 'DANVILLE', 'percent' => TAX_RATE_US_IL_DANVILLE],
    'Vertex_Jurisdiction_IL_Cook' => ['label' => 'COOK', 'percent' => TAX_RATE_US_IL_COOK],
    'Vertex_Jurisdiction_IL_River_Grove' => ['label' => 'RIVER GROVE', 'percent' => TAX_RATE_US_IL_RIVER_GROVE],
    'Vertex_Jurisdiction_IL_Cook_RTA' => [
        'label' => 'REGIONAL TRANSPORTATION AUTHORITY (COOK)',
        'percent' => TAX_RATE_US_IL_COOK_RTA
    ]
];

$xmlBase = <<<XML
<?xml version="1.0" encoding="UTF-8" ?>
<!--
 /**
  * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
  * @author     Mediotype                     https://www.mediotype.com/
  */
-->
<entities xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
          xsi:noNamespaceSchemaLocation="urn:magento:mftf:DataGenerator/etc/dataProfileSchema.xsd">
          <!-- WARNING: This file is autogenerated.  Please do not modify -->
</entities>
XML;

$entitiesXML = new SimpleXMLElement($xmlBase);

foreach ($entities as $entityName => $entity) {
    $entityXML = $entitiesXML->addChild('entity');
    $entityXML->addAttribute('name', $entityName);
    foreach ($entity as $dataKey => $dataValue) {
        $dataXML = $entityXML->addChild(
            'data',
            is_numeric($dataValue) && $dataKey !== 'taxPercent'
                ? number_format(round($dataValue, 2, PHP_ROUND_HALF_DOWN), 2)
                : $dataValue
        );
        $dataXML->addAttribute('key', $dataKey);
    }
}

foreach ($totals as $entityName => $data) {
    $entityXML = $entitiesXML->addChild('entity');
    $entityXML->addAttribute('name', $entityName);

    $keys = array_reduce(
        $data['pieces'],
        static function (array $carry, $piece) use ($entities) {
            $piece = $entities[$piece];
            $carry['subtotal'] += $piece['rowTotal'] ?? $piece['subtotal'];
            $carry['total'] += $piece['rowTotal'] ?? $piece['subtotal'];
            $carry['subtotalInclTax'] += $piece['rowTotalInclTax'] ?? $piece['subtotalInclTax'];
            $carry['totalInclTax'] += $piece['rowTotalInclTax'] ?? $piece['subtotalInclTax'];
            $carry['tax'] += $piece['rowTax'] ?? $piece['tax'];
            $carry['productTax'] += $piece['rowTax'] ?? $piece['tax'];
            return $carry;
        },
        ['subtotal' => 0, 'subtotalInclTax' => 0, 'total' => 0, 'totalInclTax' => 0, 'tax' => 0, 'productTax' => 0]
    );
    foreach ($data as $key => $value) {
        if (!is_object($value) && !is_array($value)) {
            $keys[$key] = $value;
        }
    }

    $keys['total'] += $data['shipping'];
    if (isset($data['taxRate'])) {
        // Rounding occurs on the order level, so we should re-calculate some totals & sub-totals if possible
        $keys['tax'] = ceil($keys['total'] * 100 * ($data['taxRate'] / 100)) / 100;
        $keys['subtotalInclTax'] = ceil($keys['subtotal'] * 100 * ($data['taxRate'] / 100 + 1)) / 100;
        $keys['productTax'] = ceil($keys['subtotal'] * 100 * ($data['taxRate'] / 100)) / 100;
        $keys['totalInclTax'] = ceil($keys['total'] * 100 * ($data['taxRate'] / 100 + 1)) / 100;
    } else {
        $keys['tax'] += $data['shippingTax'];
        $keys['totalInclTax'] += $data['shippingInclTax'];
    }

    foreach ($keys as $dataKey => $dataValue) {
        if ($dataKey === 'taxRate') {
            continue;
        }
        $dataXML = $entityXML->addChild(
            'data',
            is_numeric($dataValue) ? number_format(round($dataValue, 2, PHP_ROUND_HALF_DOWN), 2) : $dataValue
        );
        $dataXML->addAttribute('key', $dataKey);
    }
}

foreach ($jurisdictions as $entityName => $data) {
    $entityXML = $entitiesXML->addChild('entity');
    $entityXML->addAttribute('name', $entityName);
    foreach ($data as $dataKey => $dataValue) {
        $dataXML = $entityXML->addChild(
            'data',
            $dataValue
        );
        $dataXML->addAttribute('key', $dataKey);
    }
}

$dom = new DOMDocument('1.0');
$dom->preserveWhiteSpace = false;
$dom->formatOutput = true;
$dom->loadXML($entitiesXML->asXML());

file_put_contents('VertexTestCaseValuesData.xml', $dom->saveXML());
