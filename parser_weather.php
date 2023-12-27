<?php

namespace WeatherApp;

use Exception;

// Кастомное исключение.
class WeatherServiceException extends Exception {}

// Определяет контракт для всех сервисов погоды.
interface WeatherService {
    public function getTemperature($city);
}

// Конкретная стратегия.
class OpenWeatherMapService implements WeatherService {
    private $apiKey;

    public function __construct($apiKey) {
        $this->apiKey = $apiKey;
    }

    public function getTemperature($city) {
        try {
            if (empty($city)) {
                throw new WeatherServiceException("City is a required parameter.");
            }

            $url = "http://api.openweathermap.org/data/2.5/" .
                   "weather?q={$city}&appid={$this->apiKey}&units=metric";

            $ch = curl_init($url);
            // Для сохранения в переменную.
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            $response = curl_exec($ch);

            if (!$response) {
                throw new WeatherServiceException("Failed to retrieve data.");
            }

            $data = json_decode($response, true);

            if (!isset($data['main']['temp'])) {
                throw new WeatherServiceException("Weather data not found");
            }

            return round($data['main']['temp']);

        } catch (WeatherServiceException $e) {
            throw new WeatherServiceException("Error: " . $e->getMessage());
        }
    }
}

// Контекст, который использует стратегию (сервис погоды).
class WeatherApp {
    private $weatherService;

    public function __construct(WeatherService $weatherService) {
        $this->weatherService = $weatherService;
    }

    public function getTemperature($city) {
        try {
            return $this->weatherService->getTemperature($city);
        } catch (WeatherServiceException $e) {
            return $e->getMessage();
        }
    }
}

// Пример использования
function main() {
    try {
        $apiKey = "YOUR API KEY";
        $openWeatherMapService = new OpenWeatherMapService($apiKey);
        $weatherApp = new WeatherApp($openWeatherMapService);

        $city = "London";
        $result = $weatherApp->getTemperature($city);

        echo "Current temperature in {$city}: {$result}°C";
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage();
    }
}

main();

