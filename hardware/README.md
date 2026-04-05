# EdgeDeck Hardware

Hardware-specific notes, wiring references, and enclosure ideas belong here.

**Authoritative reference:** [M5Stack Cardputer-Adv documentation](https://docs.m5stack.com/en/core/Cardputer-Adv) (includes schematics PDFs).

Core: **Stamp-S3A** (ESP32-S3FN8). GPIO labels below are the **Stamp-S3A** pin names as used in M5Stack docs (`G1` … `G46`).

## Cardputer ADV pin mapping

### LCD (ST7789V2, 240×135)

| GPIO | Function |
| --- | --- |
| G38 | DISP_BL (backlight) |
| G33 | RST |
| G34 | RS (DC) |
| G35 | DAT (MOSI) |
| G36 | SCK |
| G37 | CS |

G38 is also **RGB LED PWR_EN** (backlight / LED power control per M5 schematic notes).

### Audio (ES8311)

| GPIO | Function |
| --- | --- |
| G8 | SDA (I²C) |
| G9 | SCL (I²C) |
| G41 | SCLK |
| G46 | ASDOUT |
| G43 | LRCK |
| G42 | DSDIN |

### IMU (BMI270)

Shared I²C with audio and keyboard: **G8** SDA, **G9** SCL.

### IR transmitter

| GPIO | Function |
| --- | --- |
| G44 | IR TX |

### Battery

| GPIO | Function |
| --- | --- |
| G10 | Battery ADC |

### Keyboard (TCA8418RTWR)

| GPIO | Function |
| --- | --- |
| G8 | SDA |
| G9 | SCL |
| G11 | INT |

### microSD

| GPIO | Function |
| --- | --- |
| G12 | CS |
| G14 | MOSI |
| G40 | CLK |
| G39 | MISO |

### HY2.0-4P (Grove-style)

| Wire | Signal |
| --- | --- |
| Black | GND |
| Red | 5V |
| Yellow | G2 |
| White | G1 |

### EXT 2.54-14P expansion bus

| Function | GPIO | | GPIO | Function |
| --- | --- | --- | --- | --- |
| RESET | G3 | · | · | 5VIN |
| INT | G4 | · | · | GND |
| BUSY | G6 | · | · | 5VOUT |
| SCK | G40 | · | · | G8 | I²C SDA |
| MOSI | G14 | · | · | G9 | I²C SCL |
| MISO | G39 | · | · | G13 | UART RX |
| CS | G5 | · | · | G15 | UART TX |

Pin order follows M5’s left/right column layout on the product page; verify against the [schematics PDF](https://m5stack-doc.oss-cn-shenzhen.aliyuncs.com/1178/Sch_M5CardputerAdv_v1.0_2025_06_20_17_19_58.pdf) before designing a custom daughterboard.

---

Suggested future contents:

- Power considerations
- External sensor/bridge modules
- Field enclosure sketches
