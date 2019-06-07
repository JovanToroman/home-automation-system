#include "espressif/esp_common.h"
#include "esp/uart.h"

#include <string.h>

#include <FreeRTOS.h>
#include <task.h>
// #include <ssid_config.h>

#include <espressif/esp_sta.h>
#include <espressif/esp_wifi.h>

#include <paho_mqtt_c/MQTTESP8266.h>
#include <paho_mqtt_c/MQTTClient.h>

#include <semphr.h>
#include "esp/uart.h"
#include "FreeRTOS.h"
#include "task.h"
#include "queue.h"
#include "esp8266.h"
#include <espressif/sdk_private.h>
#include <stdbool.h>
#include "i2c/i2c.h"
#include "bmp280/bmp280.h"
#define I2C_BUS 0
#define SCL_PIN 14
#define SDA_PIN 12
#define CS_LO 15
#define CS_NRF 0
#define PCF_ADDRESS 0x38
#define button1 0x20 // 0b ??0? ????
#define button2 0x10 // 0b ???0 ????
#define button3 0x80 // 0b 0??? ????
#define button4 0x40
#define led1 0xfe // 0b ???? ???0
#define led2 0xfd // 0b ???? ??0?
#define led3 0xfb // 0b ???? ?0??
#define led4 0xf7 // 0b ???? 0???


/* You can use http://test.mosquitto.org/ to test mqtt_client instead
 * of setting up your own MQTT server */
#define MQTT_HOST ("broker.hivemq.com")
#define MQTT_PORT 1883

#define MQTT_USER NULL
#define MQTT_PASS NULL
#define MQTT_PUBLISH_TOPIC ("door31071993")
#define MQTT_LISTEN_TOPIC ("restart31071993")

#define WIFI_SSID ("Internet")
#define WIFI_PASS ("StudentNajBo!")

#define PUB_MSG_LEN 7

#define LED1 2

SemaphoreHandle_t wifi_alive;
QueueHandle_t publish_queue;
TaskHandle_t running_light_h;

const uint8_t mpu9250_adress = 0x68;

int x_acc_sum = 0;
int y_acc_sum = 0;
int z_acc_sum = 0;

uint8_t reg_pwr_mng = 0x6c;
uint8_t data_pwr_mng = 0x00;
uint8_t acc_conf0_address = 0x1c;
uint8_t acc_conf1_address = 0x1d;
uint8_t acc_conf0_payload = 0x00;
uint8_t acc_conf1_payload = 0x05;

static bool  publishDoorState(bool isOpened)
{
    char msg[PUB_MSG_LEN];
    int count = 0;

    if (isOpened) {
        printf("Open signal sent\n");
        snprintf(msg, PUB_MSG_LEN, "%s", "opened");
    } else {
        printf("Close signal sent\n");
        snprintf(msg, PUB_MSG_LEN, "%s", "closed");
    }
    if (xQueueSend(publish_queue, (void *)msg, 0) == pdFALSE) {
        printf("Publish queue overflow.\r\n");
        return false;
    }
    return true;
}

void doorTask (void *pvParameters){

		const uint16_t MAX_NOISE = 300;
		const uint16_t MAX_SIGNAL = 1000;

		i2c_slave_write(I2C_BUS, mpu9250_adress, &reg_pwr_mng, &data_pwr_mng, 1);
		i2c_slave_write(I2C_BUS, mpu9250_adress, &acc_conf0_address, &acc_conf0_payload, 1);
		i2c_slave_write(I2C_BUS, mpu9250_adress, &acc_conf1_address, &acc_conf1_payload, 1);
		int8_t z_h, z_l;
		int16_t z_acc;
		uint8_t z_acc_h = 0x3F;
		uint8_t z_acc_l = 0x40;

		bool isDoorOpened = false;
		bool openingInitiated = false;
        bool isMessSent = false;
		int32_t openingWeight = 0;
		// used to get average value
		int16_t values_z[10];
		int32_t average = 0;
		uint8_t counter = 0;
		for (uint8_t i = 0; i < 10; i++) {
			i2c_slave_read(I2C_BUS, mpu9250_adress, &z_acc_h, &z_h, 1);
			i2c_slave_read(I2C_BUS, mpu9250_adress, &z_acc_l, &z_l, 1);
			values_z[i] = (z_h << 8) + z_l - z_acc_sum;
			vTaskDelay(100 / portTICK_PERIOD_MS);
		}

		for (uint8_t i = 0; i < 10; i++) {
			average += values_z[i];
		}
		average /= 10;

	while (1) {
		i2c_slave_read(I2C_BUS, mpu9250_adress, &z_acc_h, &z_h, 1);
		i2c_slave_read(I2C_BUS, mpu9250_adress, &z_acc_l, &z_l, 1);
		z_acc = (z_h << 8) + z_l - z_acc_sum;
		
		int32_t diff_signed = z_acc - average;
		int16_t const mask = diff_signed >> sizeof(int16_t) * CHAR_BIT - 1;
		uint16_t diff_abs =  diff_signed ^ mask;
		printf("Diff: %d\n", diff_abs);
		printf("Average: %d\n", average);
		printf("Z: %d\n", z_acc);

		if (!openingInitiated) {
            if (!isMessSent) {
                isMessSent = publishDoorState(false);
            }
			if (diff_signed > MAX_NOISE) {
                isMessSent = false;
				openingInitiated = true;
			} else {
				values_z[counter] = z_acc;
				average = 0;
				for (uint8_t i = 0; i < 10; i++) {
					average += values_z[i];
				}
				average /= 10;
				counter = (counter + 1) % 10;
			}
		}

		if (!isDoorOpened && openingInitiated) {
			printf("Opening initiated\n");
			if (diff_signed > MAX_NOISE) {
				openingWeight += diff_signed;
				isDoorOpened = true;
			}
		}
		printf("Opening weight: %d\n", openingWeight);
		
		if (isDoorOpened) {
			printf("Closing initiated\n");
            if (!isMessSent) {
                isMessSent = publishDoorState(true);
            }
			if (diff_signed < -MAX_NOISE && diff_signed > -MAX_SIGNAL) {
				openingWeight += diff_signed;
			} else if (diff_signed > MAX_NOISE && diff_signed < MAX_SIGNAL) {
				openingWeight += 0.07 * diff_signed;
			}
			if (openingWeight < 500) {
				printf("Door is closed\n");  
				isDoorOpened = false;
				openingInitiated = false;
				openingWeight = 0;
                isMessSent = false;
			}
		}



		gpio_write(LED1, isDoorOpened);
        if (isDoorOpened) {
            vTaskResume(running_light_h);
        } else {
            vTaskSuspend(running_light_h);
        }
		vTaskDelay(500 / portTICK_PERIOD_MS);

	}

}

static void  topic_received(mqtt_message_data_t *md)
{
    int i;
    mqtt_message_t *message = md->message;
    printf("Received: ");
    for( i = 0; i < md->topic->lenstring.len; ++i)
        printf("%c", md->topic->lenstring.data[ i ]);

    printf(" = ");
    char mess[] = "reset";
    bool isReset = true;
    for( i = 0; i < (int)message->payloadlen; ++i) {
        printf("%c", ((char *)(message->payload))[i]);
        if (((char *)(message->payload))[i] != mess[i]) {
            isReset = false;
        }
    }
    if (isReset) {
        sdk_system_restart();
    }

    printf("\r\n");
}

static const char *  get_my_id(void)
{
    // Use MAC address for Station as unique ID
    static char my_id[13];
    static bool my_id_done = false;
    int8_t i;
    uint8_t x;
    if (my_id_done)
        return my_id;
    if (!sdk_wifi_get_macaddr(STATION_IF, (uint8_t *)my_id))
        return NULL;
    for (i = 5; i >= 0; --i)
    {
        x = my_id[i] & 0x0F;
        if (x > 9) x += 7;
        my_id[i * 2 + 1] = x + '0';
        x = my_id[i] >> 4;
        if (x > 9) x += 7;
        my_id[i * 2] = x + '0';
    }
    my_id[12] = '\0';
    my_id_done = true;
    return my_id;
}

static void  mqtt_task(void *pvParameters)
{
    int ret         = 0;
    struct mqtt_network network;
    mqtt_client_t client   = mqtt_client_default;
    char mqtt_client_id[20];
    uint8_t mqtt_buf[100];
    uint8_t mqtt_readbuf[100];
    mqtt_packet_connect_data_t data = mqtt_packet_connect_data_initializer;

    mqtt_network_new( &network );
    memset(mqtt_client_id, 0, sizeof(mqtt_client_id));
    strcpy(mqtt_client_id, "ESP-");
    strcat(mqtt_client_id, get_my_id());

    while(1) {
        xSemaphoreTake(wifi_alive, portMAX_DELAY);
        printf("%s: started\n\r", __func__);
        printf("%s: (Re)connecting to MQTT server %s ... ",__func__,
               MQTT_HOST);
        ret = mqtt_network_connect(&network, MQTT_HOST, MQTT_PORT);
        if( ret ){
            printf("error: %d\n\r", ret);
            taskYIELD();
            continue;
        }
        printf("done\n\r");
        mqtt_client_new(&client, &network, 5000, mqtt_buf, 100,
                      mqtt_readbuf, 100);

        data.willFlag       = 0;
        data.MQTTVersion    = 3;
        data.clientID.cstring   = mqtt_client_id;
        data.username.cstring   = MQTT_USER;
        data.password.cstring   = MQTT_PASS;
        data.keepAliveInterval  = 10;
        data.cleansession   = 0;
        printf("Send MQTT connect ... ");
        ret = mqtt_connect(&client, &data);
        if(ret){
            printf("error: %d\n\r", ret);
            mqtt_network_disconnect(&network);
            taskYIELD();
            continue;
        }
        printf("done\r\n");
        mqtt_subscribe(&client, MQTT_LISTEN_TOPIC, MQTT_QOS1, topic_received);
        xQueueReset(publish_queue);

        while(1){

            char msg[PUB_MSG_LEN - 1] = "\0";
            while(xQueueReceive(publish_queue, (void *)msg, 0) ==
                  pdTRUE){
                // printf("got message to publish\r\n");
                mqtt_message_t message;
                message.payload = msg;
                message.payloadlen = PUB_MSG_LEN;
                message.dup = 0;
                message.qos = MQTT_QOS1;
                message.retained = 0;
                ret = mqtt_publish(&client, MQTT_PUBLISH_TOPIC, &message);
                if (ret != MQTT_SUCCESS ){
                    printf("error while publishing message: %d\n", ret );
                    break;
                }
            }

            ret = mqtt_yield(&client, 1000);
            if (ret == MQTT_DISCONNECTED)
                break;
        }
        printf("Connection dropped, request restart\n\r");
        mqtt_network_disconnect(&network);
        taskYIELD();
    }
}

static void  wifi_task(void *pvParameters)
{
    uint8_t status  = 0;
    uint8_t retries = 30;
    struct sdk_station_config config = {
        .ssid = WIFI_SSID,
        .password = WIFI_PASS,
    };

    printf("WiFi: connecting to WiFi\n\r");
    sdk_wifi_set_opmode(STATION_MODE);
    sdk_wifi_station_set_config(&config);

    while(1)
    {
        while ((status != STATION_GOT_IP) && (retries)){
            status = sdk_wifi_station_get_connect_status();
            printf("%s: status = %d\n\r", __func__, status );
            if( status == STATION_WRONG_PASSWORD ){
                printf("WiFi: wrong password\n\r");
                break;
            } else if( status == STATION_NO_AP_FOUND ) {
                printf("WiFi: AP not found\n\r");
                break;
            } else if( status == STATION_CONNECT_FAIL ) {
                printf("WiFi: connection failed\r\n");
                break;
            }
            vTaskDelay( 1000 / portTICK_PERIOD_MS );
            --retries;
        }
        if (status == STATION_GOT_IP) {
            printf("WiFi: Connected\n\r");
            xSemaphoreGive( wifi_alive );
            taskYIELD();
        }

        while ((status = sdk_wifi_station_get_connect_status()) == STATION_GOT_IP) {
            xSemaphoreGive( wifi_alive );
            taskYIELD();
        }
        printf("WiFi: disconnected\n\r");
        sdk_wifi_station_disconnect();
        vTaskDelay( 1000 / portTICK_PERIOD_MS );
    }
}

void runningLight(void *pvParameters)
{
    while(1) {
        // printf("Start running\n");
		uint8_t data4 = led4; 
        uint8_t data1 = led1;
		i2c_slave_write(I2C_BUS, PCF_ADDRESS, NULL, &data4, 0); 
		i2c_slave_write(I2C_BUS, PCF_ADDRESS, NULL, &data1, 1); 
		vTaskDelay(200/portTICK_PERIOD_MS);
		uint8_t data2 = led2;
		i2c_slave_write(I2C_BUS, PCF_ADDRESS, NULL, &data1, 0); 
		i2c_slave_write(I2C_BUS, PCF_ADDRESS, NULL, &data2, 1); 
		vTaskDelay(200/portTICK_PERIOD_MS);
		uint8_t data3 = led3;
		i2c_slave_write(I2C_BUS, PCF_ADDRESS, NULL, &data2, 0); 
		i2c_slave_write(I2C_BUS, PCF_ADDRESS, NULL, &data3, 1);
		vTaskDelay(200/portTICK_PERIOD_MS);
		i2c_slave_write(I2C_BUS, PCF_ADDRESS, NULL, &data3, 0);
		i2c_slave_write(I2C_BUS, PCF_ADDRESS, NULL, &data4, 1); 
		vTaskDelay(200/portTICK_PERIOD_MS);
    }
}

void user_init(void)
{
    uart_set_baud(0, 115200);
    printf("SDK version:%s\n", sdk_system_get_sdk_version());
    uart_set_baud(0, 115200);
    gpio_enable(CS_LO, GPIO_OUTPUT);
    gpio_enable(CS_NRF, GPIO_OUTPUT);
    gpio_write(CS_LO, 1);
    gpio_write(CS_NRF, 1);
    i2c_init(I2C_BUS, SCL_PIN, SDA_PIN, 100);
    gpio_enable(SCL_PIN, GPIO_OUTPUT);

    vSemaphoreCreateBinary(wifi_alive);
    publish_queue = xQueueCreate(3, PUB_MSG_LEN);
    xTaskCreate(&wifi_task, "wifi_task",  256, NULL, 2, NULL);
    xTaskCreate(&doorTask, "doorTask", 256, NULL, 3, NULL);
    xTaskCreate(&mqtt_task, "mqtt_task", 1024, NULL, 4, NULL);
    xTaskCreate(runningLight,"Task2",256,NULL,2,&running_light_h);
	vTaskSuspend(running_light_h);
}