<?php declare(strict_types=1);
/**
 * @copyright  Vertex. All rights reserved.  https://www.vertexinc.com/
 * @author     Mediotype                     https://www.mediotype.com/
 */

namespace Vertex\Test\Integration\Mapper\Utility;

use Vertex\Mapper\MapperFactory;

/**
 * Provides methods for creating dataProvider-compatible arrays with mappers
 */
class CommonMapperProvider
{
    /**
     * Generate a dataProvider compatible array of mappers for a given type
     *
     * The key will be the API Level of the Mapper like "API Level 70 Mapper" and the value will be an array
     * containing a single index (0) which contains a Mapper for the given interface at the API Level specified in
     * the key
     *
     * @param string $type FQN of the interface that will be mapped
     * @return array
     */
    public static function getAllMappers($type)
    {
        $factory = new MapperFactory();

        $apiLevels = ['60', '70'];

        $result = [];
        foreach ($apiLevels as $level) {
            $result['API Level ' . $level . ' Mapper'] = [$factory->getForClass($type, $level)];
        }

        return $result;
    }

    /**
     * Generate a dataProvider compatible array of mappers unioned with provided data
     *
     * The key will be a combination of the mapper key and the key from the provided data, the value will be the
     * result of an array_merge of the mapper to the data arguments
     *
     * @param string $mapperType FQN of the interface that will be mapped
     * @param array $data dataProvider compatible array of data to union with it
     * @return array
     */
    public static function getAllMappersWithProvidedData($mapperType, array $data)
    {
        $result = [];
        foreach (self::getAllMappers($mapperType) as $mapperKey => $mapperArguments) {
            foreach ($data as $dataKey => $dataArguments) {
                $dataKey = is_string($dataKey) ? $dataKey : "data set #{$dataKey}";
                $result["{$mapperKey} x {$dataKey}"] = array_merge($mapperArguments, $dataArguments);
            }
        }
        return $result;
    }

    /**
     * Use the random_int if the php version allows
     *
     * @param int $min
     * @param int $max
     * @return int|mixed
     * @throws \Exception
     */
    public static function randBasedOnMethodAvaialbility($min = 0, $max = PHP_INT_MAX)
    {
        if (function_exists('random_int')) {
            return random_int($min, $max);
        }

        return mt_rand($min, $max);
    }
}
