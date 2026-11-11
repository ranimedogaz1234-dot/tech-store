const staticProducts = [
    {
        id: 1,
        name: 'Casque Micro Gaming Spirit of Gamer PRO-H6 WHITE / RGB',
        price: 70.00,
        oldPrice: null,
        category: 'peripherals',
        subcategory: 'headset',
        brand: 'Spirit Of Gamer',
        badge: null,
        bestseller: true,
        featured: false,
        colors: ['white', 'black'],
         imagesByColor: {
        white: [
            'img/casb.png',
            'img/casque4b.png',
            'img/casque5b.png'
        ],
        black: [
            'img/casque-micro-gaming-spirit-of-gamer-pro-h6-dark-rgb-noir.png',
            'img/casque3n.png',
            'img/casque2n.png'
        ]
        },
        image:'img/casb.png',
        sku: 'N/A',
        description: 'Casque Micro Gaming Spirit of Gamer PRO-H6 DARK – Rétro-éclairage RGB – Norme Audio: Stéréo/Windows Sonic – Connectique principale: Câble Jack 3.5 mm / Prise USB pour le rétro-éclairage uniquement – Type de casque: Circum-aural – Contrôles: Mute Microphone directement sur le micro / Volume sur l\'écouteur gauche – Diamètre des Haut-Parleurs: 50 mm – Sensibilité casque: 112 dB (+/-3dB) – Fréquence casque: 20~20 KHz – Impédance casque: 32 Ohms – Type de micro: Flexible, Omnidirectionnel, Réduction de bruit – Sensibilité microphone: -42 dB (+/-3dB) – Impédance microphone: 2 – Compatibilité: Consoles, PC, Smartphones – Matériaux: Bandeau, Base arceau, Coussinets HP – Poids: 240 g – Couleur Blanc – Garantie 1 an',
        specifications: {
            'Audio': 'Stéréo/Windows Sonic',
            'Connection': 'Câble Jack 3.5 mm',
            'Type': 'Circum-aural',
            'Diameter': '50 mm',
            'Weight': '240 g',
            'Warranty': '1 an'
        }

    },

    {
        id: 2,
        name: 'Clavier NuPhy Gem80',
        price: 412000.00,
        oldPrice: null,
        category: 'peripherals',
        subcategory: 'keyboard',
        brand: 'NuPhy',
        badge: null,
        bestseller: true,
        featured: false,
        colors: ['pink','purple','beige'  ],
         imagesByColor: {pink: ['img/nuphyrose.webp',
            'img/nuphyrose1.webp',
            'img/nuphyrose2.webp',
            'img/nuphy.webp'


        ],purple: [ 'img/claviernuphy.webp', 'img/nuphymauv1.webp','img/nuphymauv2.webp',
            'img/nuphy.webp'


        ],
        beige: ['img/nuphymaro2.jpg','img/nuphymaro.webp','img/nuphymarron.webp',
            'img/nuphy.webp',



        ]

        },
        image: 'img/claviernuphy.webp',
        sku: 'N/A',
        description: 'Omni-Mojo. Montage supérieur, montage sur joint ou montage inférieur ? Grâce à la technologie pionnière de montage omnidirectionnel, prenez tout le temps du monde pour trouver le style de montage qui correspond à vos préférences en matière de son et de sensation sur Gem80, avec le kit de joint et de montage complémentaire.',
        specifications: {
            'Layout': '80% TKL',
            'Connection': 'USB-C / Wireless',
            'Switch': 'Hot-swappable',
            'Material': 'Aluminum'
        }
    },
    {
        id: 3,
        name: 'HUAWEI MateBook X Pro U9-185H UMA 32GB + 2TB Morandi Blue',
        price: 7079000.00,
        oldPrice: 7679000.00,
        image: 'img/huawilap.png',
        category: 'laptop',
        subcategory: 'ultrabook',
        brand: 'HUAWEI',
        badge: '-8%',
        bestseller: true,
        featured: true,
        colors: [],
        sku: 'N/A',
        description: 'Ecran 16" WQXGA (2560 x 1600), IPS, antireflet, 240 Hz, DisplayHDR 400, Dolby Vision, G-SYNC…',
        specifications: {
            'Processor': 'Intel Core U9-185H',
            'RAM': '32GB',
            'Storage': '2TB SSD',
            'Screen': '16" WQXGA',
            'Graphics': 'Integrated UMA'
        }
    },
    {
        id: 4,
        name: 'Logitech Brio 100 Webcam Full HD',
        price: 105000.00,
        oldPrice: null,
        image: 'img/logitech.webp',
        category: 'peripherals',
        subcategory: 'webcam',
        brand: 'LogiTech',
        badge: null,
        bestseller: true,
        featured: false,
        colors: [],
        sku: 'N/A',
        description: 'Webcam Full HD 1080p avec correction automatique de la lumière',
        specifications: {
            'Resolution': '1080p Full HD',
            'FPS': '30fps',
            'Field of View': '78°',
            'Connection': 'USB-A'
        }
    },
    {
        id: 5,
        name: 'Pc Portable Gamer Lenovo Legion Pro 7 16IRX9H / i9-14900HX / RTX 4090 16G / 64 Go DDR5 / 1 To SSD / Windows 11 / Noir Avec Casque Lenovo Legion H300',
        price: 12990000.00,
        oldPrice: 13456000.00,
        image: 'img/lenovopc.png',
        category: 'laptop',
        subcategory: 'gaming',
        brand: 'Lenovo',
        badge: '-3%',
        bestseller: true,
        featured: false,
        colors: [],
        sku: 'N/A',
        description: 'Intel Core i7 13700H : Puissance et vitesse pour toute tâche à laquelle vous êtes confronté - 16 Go de RAM et 512 Go de SSD : Multitâche fluide et vitesses d\'accès rapides - Écran FHD 15,6\" : Des visuels époustouflants dans un Full HD vibrant - Durabilité de qualité militaire : Conçu pour résister aux défis quotidiens - Puissance longue durée : Restez productif en déplacement grâce à une autonomie prolongée - Système d\'exploitation Windows 11 : Restez productif avec une interface et des fonctionnalités modernes',
        specifications: {
            'Processor': 'Intel Core i9-14900HX',
            'GPU': 'NVIDIA RTX 4090 16GB',
            'RAM': '64GB DDR5',
            'Storage': '1TB SSD',
            'Screen': '16" QHD+',
            'OS': 'Windows 11'
        }
    },
    {
        id: 6,
        name: 'SOURIS PRO-M5 RGB 8000DPI WHITE',
        price: 69.00,
        oldPrice: null,
        image: 'img/souris.png',
        category: 'peripherals',
        subcategory: 'mouse',
        brand: 'Spirit Of Gamer',
        badge: null,
        bestseller: false,
        featured: false,
        colors: [],
        sku: 'N/A',
        description: 'SOURIS PRO-M5 RGB 8000DPI WHITE - Spirit of Gamer',
        specifications: {
            'DPI': '8000',
            'Connection': 'USB',
            'RGB': 'Yes',
            'Color': 'White'
        }
    },
    {
        id: 7,
        name: 'Apple MacBook Pro 16,2 pouces M4 Max 48 Go de RAM SSD 1 To – Noir spatial',
        price: 14099000.00,
        oldPrice: 14300000.00,
        image: 'img/applemac.jpg',
        category: 'laptop',
        subcategory: 'professional',
        brand: 'Apple',
        badge: '-2%',
        bestseller: false,
        featured: true,
        colors: [],
        sku: 'N/A',
        description: 'Apple MacBook Pro avec puce M4 Max',
        specifications: {
            'Processor': 'Apple M4 Max',
            'RAM': '48GB',
            'Storage': '1TB SSD',
            'Screen': '16.2"',
            'Color': 'Space Black'
        }
    },
    {
        id: 8,
        name: 'Bange BgD12',
        price: 499000.00,
        oldPrice: 800000.00,
        image: 'img/pang.webp',
        images: [
        'img/pang.webp',
        'img/bang2.webp',
        'img/bang3.webp'
        ],
        category: 'accessories',
        subcategory: 'bag',
        brand: 'BANGE',
        badge:'-38%',
        bestseller: false,
        featured: true,
        colors: [],
        sku: 'N/A',
        description: 'Sac à dos pour ordinateur portable BANGE',
        specifications: {
            'Capacity': '15-17"',
            'Material': 'Water-resistant',
            'Compartments': 'Multiple'
        }
    },
    {
        id: 9,
        name: 'Apple AirPods Max',
        price: 1645000.00,
        oldPrice: null,
        image: 'img/APPECMAU.jpg',
        category: 'peripherals',
        subcategory: 'headset',
        brand: 'Apple',
        badge: null,
        bestseller: true,
        featured: false,
        colors: ['black','purple','beige',"orange","blue"  ],
         imagesByColor: {black: ['img/APPLEECTn.jpg',
            'img/APPEC2.jpg',
            'img/appbla.jpg',
            'img/APPEC7.jpg'


        ],purple: [ 'img/APPECMAU.jpg',
            'img/APPECMAU1.jpg',
            'img/appmau.jpg',
            'img/APPEC7.jpg'


        ],
        beige: ['img/APPEC.jpg',
            'img/APPECB.jpg',
            'img/appbei.jpg',
            'img/APPEC7.jpg'
            
            
        ],        
        blue: ['img/APPEC1.jpg',
            'img/appblu.jpg',
            'img/APPEC7.jpg'
            
            
        ] ,
        orange: ['img/APPECO.jpg','img/APPECO1.jpg',
            'img/appora.jpg',
            'img/APPEC7.jpg'
            
            
        ] 
        },
        sku: 'N/A',
        description: 'Apple AirPods Max - Casque audio sans fil avec réduction de bruit active',
        specifications: {
            'Type': 'Over-ear',
            'Connection': 'Bluetooth',
            'Noise Cancellation': 'Active',
            'Battery Life': '20 hours'
        }
    },
    {
        id: 10,
        name: 'APPLE MacBook Air M4 16Go 256Go SSD – Lumière stellaire',
        price: 4349000.00,
        oldPrice: null,
        image: 'img/air.jpg',
        category: 'laptop',
        subcategory: 'ultrabook',
        brand: 'Apple',
        badge: null,
        bestseller: true,
        featured: false,
        colors: [],
        sku: 'N/A',
        description: 'Apple MacBook Air avec puce M4',
        specifications: {
            'Processor': 'Apple M4',
            'RAM': '16GB',
            'Storage': '256GB SSD',
            'Screen': '13.6"',
            'Color': 'Starlight'
        }
    },
    {
        id: 11,
        name: 'APPLE MacBook Air M4 16Go 256Go SSD – Silver',
        price: 4249000.00,
        oldPrice: 4349000.00,
        image: 'img/airse.jpg',
        category: 'laptop',
        subcategory: 'ultrabook',
        brand: 'Apple',
        badge: '5%',
        bestseller: false,
        featured: true,
        colors: [],
        sku: 'N/A',
        description: 'Apple MacBook Air avec puce M4',
        specifications: {
            'Processor': 'Apple M4',
            'RAM': '16GB',
            'Storage': '256GB SSD',
            'Screen': '13.6"',
            'Color': 'Silver'
        }
    },
    {
        id: 12,
        name: 'collection apple macbook air noir+iphone 8 plus noir +apple watch noir',
        price: 7503200.00,
        oldPrice: 9379000.00,
        image: 'img/WhatsApp-Image-2025-12-01-a-00.42.52_7971af03.jpg',
        images: [
        'img/telephone-portable-apple-iphone-8-plus-64-go-noir.jpg',
        'img/WhatsApp-Image-2025-12-01-a-00.55.31_8b8b0320.jpg',
        'img/apple-watch-series-6-gps-40mm-space-gray-aluminium-avec-black-sport-band.png'
        ],
        category: 'accessories',
        subcategory: 'bundle',
        brand: 'Apple',
        badge: '-20%',
        bestseller: false,
        featured: true,
        colors: [],
        sku: 'N/A',
        description: 'Collection Apple - MacBook Air, iPhone 8 Plus et Apple Watch en noir',
        specifications: {
            'Bundle': 'MacBook Air + iPhone 8 Plus + Apple Watch',
            'Color': 'Space Black'
        }
    },
    {
        id: 13,
        name: 'BOITIER GAMING GHOST ONE RGB',
        price: 170.00,
        oldPrice: 180.00,
        image: 'img/5437-thickbox_default-BOITIER-GAMING-GHOST-ONE-RGB.jpg',
        category: 'desktop',
        subcategory: 'case',
        brand: 'Spirit Of Gamer',
        badge: '-6%',
        bestseller: false,
        featured: false,
        colors: [],
        sku: 'N/A',
        description: 'Boîtier Gaming Ghost One RGB avec panneau vitré',
        specifications: {
            'Type': 'Mid Tower',
            'RGB': 'Yes',
            'Side Panel': 'Tempered Glass'
        }
    },
    {
        id: 14,
        name: 'BOITIER GAMING RAIDMAX SIGMA Prime AI4RTB',
        price:400.000,
        oldPrice: null,
        image: 'img/raidmax-sigma-5-500x500-1.jpg',
        category: 'desktop',
        subcategory: 'case',
        brand: 'RAIDMAX',
        badge: null,
        bestseller: false,
        featured: false,
        colors: [],
        sku: 'N/A',
        description: 'Boîtier Gaming RAIDMAX Sigma Prime AI4RTB avec éclairage RGB',
        specifications: {
            'Type': 'Mid Tower',
            'RGB': 'Yes',
            'Side Panel': 'Tempered Glass'
        }
    },
    {
        id: 15,
        name: 'CLAVIER GAMER MSI VIGOR GK20 / RGB',
        price: 64.00,
        oldPrice: null,
        image: 'img/600-300x300.png',
        category: 'peripherals',
        subcategory: 'keyboard',
        brand: 'MSI',
        badge: null,
        bestseller: false,
        featured: false,
        colors: [],
        sku: 'N/A',
        description: 'Clavier Gaming MSI Vigor GK20 avec rétro-éclairage RGB',
        specifications: {
            'Layout': 'Full Size',
            'RGB': 'Yes',
            'Connection': 'USB'
        }
    },
    {
        id: 16,
        name: 'CLAVIER GAMING WHITE SHARK GK-1623 GLADIATOR',
        price: 58.00,
        oldPrice: null,
        image: 'img/7630-thickbox_default-CLAVIER-GAMING-WHITE-SHARK-GK-1623-GLADIATOR-REF-GK-1623-600x600.jpg',
        category: 'peripherals',
        subcategory: 'keyboard',
        brand: 'White Shark',
        badge: null,
        bestseller: false,
        featured: false,
        colors: [],
        sku: 'N/A',
        description: 'Clavier Gaming White Shark Gladiator avec rétro-éclairage RGB',
        specifications: {
            'Layout': 'Full Size',
            'RGB': 'Yes',
            'Connection': 'USB'
        }
    },
    {
        id: 17,
        name: 'Écran 27" Full HD Lenovo ThinkVision S27i-30',
        price: 479.00,
        oldPrice: 499.00,
        image: 'img/ecran-lenovo-think-vision-s27i-30-27-full-hd-ips-100-hz-600x600.png',
        category: 'peripherals',
        subcategory: 'monitor',
        brand: 'Lenovo',
        badge: '-4%',
        bestseller: false,
        featured: false,
        colors: [],
        sku: 'N/A',
        description: 'Écran 27" Full HD Lenovo ThinkVision S27i-30',
        specifications: {
            'Size': '27"',
            'Resolution': 'Full HD 1920x1080',
            'Panel': 'IPS',
            'Refresh Rate': '75Hz'
        }
    },
    {
        id: 18,
        name: 'ECRAN Dell 22 Monitor SE2216H – 54,5Cm (21,5") Black2',
        price: 399.00,
        oldPrice: 499.00,
        image: 'img/7ae84c19a3454538cda6aaba2652fa5a-300x300.jpg',
        category: 'peripherals',
        subcategory: 'monitor',
        brand: 'Dell',
        badge: '-20%',
        bestseller: false,
        featured: false,
        colors: [],
        sku: 'N/A',
        description: 'Écran Dell 22" SE2216H Full HD',
        specifications: {
            'Size': '21.5"',
            'Resolution': 'Full HD',
            'Panel': 'IPS'
        }
    },
    {
        id: 20,
        name: 'Ecran Gaming LENOVO Legion R25f-30 24.5" Full HD 240 Hz',
        price: 649.00,
        oldPrice: 749.00,
        image: 'img/ecran-gaming-lenovo-legion-r25f-30-24-5-full-hd-240-hz-55555555-600x600.png',
        category: 'peripherals',
        subcategory: 'monitor',
        brand: 'Lenovo',
        badge: '-13%',
        bestseller: false,
        featured: false,
        colors: [],
        sku: 'N/A',
        description: 'Écran Gaming LENOVO Legion R25f-30 24.5" Full HD 240 Hz',
        specifications: {
            'Size': '24.5"',
            'Resolution': 'Full HD',
            'Panel': 'IPS',
            'Refresh Rate': '240Hz'
        }
    },
    {
        id: 21,
        name: 'ECRAN LENOVO THINKVISION S22E-20 / 21.5" / FULL HD',
        price: 324.00,
        oldPrice: 375.00,
        image: 'img/images-14.jpg',
        category: 'peripherals',
        subcategory: 'monitor',
        brand: 'Lenovo',
        badge: '-14%',
        bestseller: false,
        featured: false,
        colors: [],
        sku: 'N/A',
        description: 'Écran LENOVO ThinkVision S22E-20 21.5" Full HD',
        specifications: {
            'Size': '21.5"',
            'Resolution': 'Full HD',
            'Panel': 'VA'
        }
    },
    {
        id: 22,
        name: 'ECRAN SAMSUNG 32" LED HDMI CURVED',
        price: 751.00,
        oldPrice: 762.00,
        image: 'img/5928-thickbox_default-ECRAN-SAMSUNG-32-LED-HDMI-CURVED-LC32F391-600x600.jpg',
        category: 'peripherals',
        subcategory: 'monitor',
        brand: 'Samsung',
        badge: '-1%',
        bestseller: false,
        featured: false,
        colors: [],
        sku: 'N/A',
        description: 'Écran SAMSUNG 32" LED HDMI CURVED',
        specifications: {
            'Size': '32"',
            'Type': 'LED',
            'Curved': 'Yes',
            'Connection': 'HDMI'
        }
    },
    {
        id: 23,
        name: 'CLAVIER + SOURIS + TAPIS SPIRIT OF GAMER PRO-MK3 SOG-3IN1',
        price: 69.00,
        oldPrice: null,
        image: 'img/ensemble-gaming-clavier-souris-et-tapis-souris-spirit-of-gamer-pro-mk3-600x600.jpg',
        category: 'peripherals',
        subcategory: 'combo',
        brand: 'Spirit Of Gamer',
        badge: null,
        bestseller: false,
        featured: false,
        colors: [],
        sku: 'N/A',
        description: 'Pack Clavier + Souris + Tapis Spirit of Gamer PRO-MK3',
        specifications: {
            'Bundle': 'Keyboard + Mouse + Mousepad',
            'RGB': 'Yes'
        }
    },
    {
        id: 24,
        name: 'Clavier Gamer Filaire Spirit of Gamer CLA-PKIFR Semi-Mécaniques',
        price: 37.00,
        oldPrice: null,
        image: 'img/clavier-gamer-filaire-spirit-of-gamer-cla-pk1fr-semi-mecaniques-rgb-3-modes-600x600.png',
        category: 'peripherals',
        subcategory: 'keyboard',
        brand: 'Spirit Of Gamer',
        badge: null,
        bestseller: false,
        featured: false,
        colors: [],
        sku: 'N/A',
        description: 'Clavier Gaming Filaire Spirit of Gamer Semi-Mécanique',
        specifications: {
            'Type': 'Semi-Mechanical',
            'RGB': 'Yes',
            'Connection': 'USB'
        }
    },
    {
        id: 25,
        name: 'Bange BG018',
        price: 359000.00,
        oldPrice: null,
        image: 'img/8b9cc7c9808a81fc8db0eaf67a4d79d7_2048x2048-600x600.webp',
        category: 'accessories',
        subcategory: 'bag',
        brand: 'BANGE',
        badge: null,
        bestseller: false,
        featured: false,
        colors: [],
        sku: 'N/A',
        description: 'Sac à dos pour ordinateur portable BANGE BG018',
        specifications: {
            'Capacity': '15-17"',
            'Material': 'Water-resistant',
            'Compartments': 'Multiple'
        }
    },
    {
        id: 26,
        name: 'Bange Bg026',
        price: 359000.00,
        oldPrice: null,
        image: 'img/66_2_2048x2048-600x600.webp',
        category: 'accessories',
        subcategory: 'bag',
        brand: 'BANGE',
        badge: null,
        bestseller: false,
        featured: false,
        colors: [],
        sku: 'N/A',
        description: 'Sac à dos pour ordinateur portable BANGE Bg026',
        specifications: {
            'Capacity': '15-17"',
            'Material': 'Water-resistant',
            'Compartments': 'Multiple'
        }
    },
    {
        id: 27,
        name: 'Bange BG047',
        price: 499000.00,
        oldPrice: 800000.00,
        image: 'img/BG026_12634cb8-e029-4a1f-9f4c-6ccc51e525e1_3_2048x2048-600x600.webp',
        category: 'accessories',
        subcategory: 'bag',
        brand: 'BANGE',
        badge: '-38%',
        bestseller: false,
        featured: false,
        colors: [],
        sku: 'N/A',
        description: 'Sac à dos pour ordinateur portable BANGE BG047',
        specifications: {
            'Capacity': '15-17"',
            'Material': 'Water-resistant',
            'Compartments': 'Multiple'
        }
    },
    {
        id: 28,
        name: 'Bange Bg099',
        price: 359000.00,
        oldPrice: null,
        image: 'img/1_e9a5f1e1-47e7-49fc-a31a-dcd1fc0bf5da.webp',
        category: 'accessories',
        subcategory: 'bag',
        brand: 'BANGE',
        badge: null,
        bestseller: false,
        featured: false,
        colors: [],
        sku: 'N/A',
        description: 'Sac à dos pour ordinateur portable BANGE Bg099',
        specifications: {
            'Capacity': '15-17"',
            'Material': 'Water-resistant',
            'Compartments': 'Multiple'
        }
    },
    {
        id: 29,
        name: 'Tapis Souris Gaming Spirit of Gamer DARKSULL M',
        price: 20.00,
        oldPrice: null,
        image: 'img/tapis-souris-gaming-spirit-of-gamer-skull-rgb-m-300x300.jpg',
        category: 'peripherals',
        subcategory: 'mousepad',
        brand: 'Spirit Of Gamer',
        badge: null,
        bestseller: false,
        featured: false,
        colors: [],
        sku: 'N/A',
        description: 'Tapis de souris Gaming Spirit of Gamer DARKSULL M',
        specifications: {
            'Size': 'Medium',
            'Surface': 'Gaming',
            'Base': 'Anti-slip'
        }
    },
    {
        id: 30,
        name: 'Casque de jeu LOGITECH G PRO X 7.1 – Noir',
        price: 176000.00,
        oldPrice: null,
        image: 'img/10195740-300x300.webp',
        category: 'peripherals',
        subcategory: 'headset',
        brand: 'Logitech',
        badge: null,
        bestseller: false,
        featured: false,
        colors: [],
        sku: 'N/A',
        description: 'Casque Gaming Logitech G PRO X 7.1 Surround',
        specifications: {
            'Audio': '7.1 Surround',
            'Connection': 'USB',
            'Microphone': 'Detachable',
            'Color': 'Black'
        }
    },
    {
        id: 31,
        name: 'Casque de jeu sans fil 7.1 LOGITECH PRO X 2 Graphene Driver LIGHTSPEED',
        price: 200000.00,
        oldPrice: 189000.00,
        image: 'img/M10251663_white-300x300.webp',
        category: 'peripherals',
        subcategory: 'headset',
        brand: 'Logitech',
        badge: '6%',
        bestseller: false,
        featured: true,
        colors: [],
        sku: 'N/A',
        description: 'Casque Gaming sans fil Logitech PRO X 2 LIGHTSPEED',
        specifications: {
            'Audio': '7.1 Surround',
            'Connection': 'Wireless LIGHTSPEED',
            'Microphone': 'Yes',
            'Color': 'White'
        }
    },
    {
        id: 32,
        name: 'LOGITECH G Astro A50 LIGHTSPEED Wireless Gaming Headset & Base Station – Black',
        price: 689000.00,
        oldPrice: 800000.00,
        image: 'img/M10212706_black-1-300x300.webp',
        category: 'peripherals',
        subcategory: 'headset',
        brand: 'Logitech',
        badge: '-14%',
        bestseller: false,
        featured: false,
        colors: [],
        sku: 'N/A',
        description: 'Casque Gaming sans fil LOGITECH G Astro A50 LIGHTSPEED avec station de base',
        specifications: {
            'Audio': 'Dolby Audio',
            'Connection': 'Wireless LIGHTSPEED',
            'Battery': '15+ hours',
            'Base Station': 'Included'
        }
    },
    {
        id: 33,
        name: 'SOG SOURIS GAMING ELITE-M30 RGB 1',
        price: 53.00,
        oldPrice: null,
        image: 'img/images-22.jpg',
        category: 'peripherals',
        subcategory: 'mouse',
        brand: 'Spirit Of Gamer',
        badge: null,
        bestseller: false,
        featured: false,
        colors: [],
        sku: 'N/A',
        description: 'Souris Gaming Spirit of Gamer ELITE-M30 RGB',
        specifications: {
            'DPI': 'Adjustable',
            'RGB': 'Yes',
            'Connection': 'USB'
        }
    },
    {
        id: 34,
        name: 'Souris Gaming Sans Fil Spirit of Gamer ELITE M50 DARK / RGB / Noir',
        price: 69.00,
        oldPrice: null,
        image: 'img/souris-gaming-sans-fil-spirit-of-gamer-elite-m50-dark-rgb-noir-600x600.png',
        category: 'peripherals',
        subcategory: 'mouse',
        brand: 'Spirit Of Gamer',
        badge: null,
        bestseller: false,
        featured: false,
        colors: [],
        sku: 'N/A',
        description: 'Souris Gaming Sans Fil Spirit of Gamer ELITE M50 RGB',
        specifications: {
            'DPI': 'Adjustable',
            'RGB': 'Yes',
            'Connection': 'Wireless'
        }
    },
    {
        id: 35,
        name: 'SOURIS SPIRIT OF GAMER SOG ELITE M50 ARMY 8 BOUTONS',
        price: 73.00,
        oldPrice: null,
        image: 'img/4460-thickbox_default-SOURIS-SPIRIT-OF-GAMER-SOG-ELITE-M50-ARMY-8-BOUTONS-REFS-EM50A-600x600.jpg',
        category: 'peripherals',
        subcategory: 'mouse',
        brand: 'Spirit Of Gamer',
        badge: null,
        bestseller: false,
        featured: false,
        colors: [],
        sku: 'N/A',
        description: 'Souris Gaming Spirit of Gamer ELITE M50 ARMY - 8 Boutons',
        specifications: {
            'DPI': 'Adjustable',
            'Buttons': '8',
            'Design': 'Army Camouflage'
        }
    },
    {
        id: 36,
        name: 'CAMERA LOGITECH WEBCAM C930E',
        price: 898000.00,
        oldPrice: null,
        image: 'img/6541-thickbox_default-CAMERA-LOGITECH-WEBCAM-C930E-600x600.jpg',
        category: 'peripherals',
        subcategory: 'webcam',
        brand: 'LogiTech',
        badge: null,
        bestseller: false,
        featured: false,
        colors: [],
        sku: 'N/A',
        description: 'Webcam Logitech C930e Full HD 1080p',
        specifications: {
            'Resolution': '1080p Full HD',
            'FPS': '30fps',
            'Field of View': '90°',
            'Connection': 'USB-A'
        }
    },
    {
        id: 37,
        name: 'Logitech Brio 300 Webcam Full HD',
        price: 160000.00,
        oldPrice: null,
        image: 'img/M10254564_white-300x300.webp',
        category: 'peripherals',
        subcategory: 'webcam',
        brand: 'LogiTech',
        badge: null,
        bestseller: false,
        featured: false,
        colors: [],
        sku: 'N/A',
        description: 'Webcam Logitech Brio 300 Full HD',
        specifications: {
            'Resolution': '1080p Full HD',
            'FPS': '30fps',
            'Connection': 'USB-C'
        }
    },
    {
        id: 38,
        name: 'Logitech StreamCam Webcam USB-C Full HD',
        price: 200000.00,
        oldPrice: null,
        image: 'img/M10205780_white-300x300.webp',
        category: 'peripherals',
        subcategory: 'webcam',
        brand: 'LogiTech',
        badge: null,
        bestseller: false,
        featured: false,
        colors: [],
        sku: 'N/A',
        description: 'Webcam Logitech StreamCam Full HD avec USB-C',
        specifications: {
            'Resolution': '1080p Full HD',
            'FPS': '60fps',
            'Connection': 'USB-C',
            'Mounting': 'Flexible'
        }
    },
    {
        id: 39,
        name: 'Webcam Full HD LOGITECH Pro C920',
        price: 170000.00,
        oldPrice: null,
        image: 'img/12236018-300x300.webp',
        category: 'peripherals',
        subcategory: 'webcam',
        brand: 'LogiTech',
        badge: null,
        bestseller: false,
        featured: false,
        colors: [],
        sku: 'N/A',
        description: 'Webcam Full HD LOGITECH Pro C920',
        specifications: {
            'Resolution': '1080p Full HD',
            'FPS': '30fps',
            'Field of View': '78°',
            'Connection': 'USB-A'
        }
    },
    {
        id: 40,
        name: 'Webcam LOGITECH MX Brio 4K Ultra HD',
        price: 759000.00,
        oldPrice: null,
        image: 'img/M10260467_graphite-300x300.webp',
        category: 'peripherals',
        subcategory: 'webcam',
        brand: 'LogiTech',
        badge: null,
        bestseller: false,
        featured: false,
        colors: [],
        sku: 'N/A',
        description: 'Webcam LOGITECH MX Brio 4K Ultra HD',
        specifications: {
            'Resolution': '4K Ultra HD',
            'FPS': '30fps at 4K',
            'Field of View': 'Adjustable',
            'Connection': 'USB-C'
        }
    }

];

let products = [...staticProducts];
async function loadProductsFromDatabase() {
    try {
        const response = await fetch('php/get_products.php');
        if (response.ok) {
            const dbProducts = await response.json();
            products = dbProducts.map(product => ({...staticProducts.find(p => p.id === product.id), ...product}));
        } else {
            console.warn('Failed to load products from database, using static products');
            products = [...staticProducts];
        }
    } catch (error) {
        console.error('Error loading products from database:', error);
        products = [...staticProducts];
    }
}

// Load products on page
async function loadBestSellers() {
    await loadProductsFromDatabase();

    const container = document.getElementById('bestSellers');
    if (!container) return;

    const bestSellers = products.filter(p => p.bestseller).slice(0, 6);
    container.innerHTML = bestSellers.map(product => createProductCard(product)).join('');
}

async function loadPromoProducts() {
    await loadProductsFromDatabase();

    const container = document.getElementById('promoProducts');
    if (!container) return;

    const promoProducts = products.filter(p => p.featured).slice(0, 6);
    container.innerHTML = promoProducts.map(product => createProductCard(product)).join('');
}

function createProductCard(product) {
    const badge = product.badge ? `<span class="product-badge">${escapeHTML(product.badge)}</span>` : '';
    const oldPrice = product.oldPrice ? `<span class="old-price">${formatPrice(product.oldPrice)}</span>` : '';
    
    return `
        <div class="product-card">
            ${badge}
            <div class="product-image">
                <img src="${escapeHTML(safeImageUrl(product.image))}" alt="${escapeHTML(product.name)}">
                <div class="product-icons">
                    <button class="product-icon" onclick="toggleWishlist(${product.id})">
                        <i class="far fa-heart"></i>
                    </button>
                    <button class="product-icon" onclick="viewProduct(${product.id})">
                        <i class="far fa-eye"></i>
                    </button>
                </div>
            </div>
            <div class="product-info">
                <h3 class="product-title">
                    <a href="product-single.html?id=${product.id}">${escapeHTML(product.name)}</a>
                </h3>
                <div class="product-price">
                    ${formatPrice(product.price)}
                    ${oldPrice}
                </div>
                <button class="add-to-cart" onclick="addProductToCart(${Number(product.id)})">
                    ADD TO BASKET
                </button>
            </div>
        </div>
    `;
}

function formatPrice(price) {
    return parseFloat(price).toLocaleString('fr-TN', { 
        minimumFractionDigits: 2,
        maximumFractionDigits: 2 
    }) + ' TND';
}

function viewProduct(productId) {
    window.location.href = `product-single.html?id=${productId}`;
}

function toggleWishlist(productId) {
    const product = products.find(p => p.id === productId);
    if (!product) return;

    let wishlist = readItems('wishlist');
    const existingIndex = wishlist.findIndex(item => item.id === productId);

    if (existingIndex > -1) {
        // Remove from wishlist
        wishlist.splice(existingIndex, 1);
        showNotification('Removed from wishlist');
    } else {
        // Add to wishlist
        const wishlistItem = {
            id: product.id,
            name: product.name,
            price: product.price,
            oldPrice: product.oldPrice,
            image: product.image,
            dateAdded: new Date().toISOString()
        };
        wishlist.push(wishlistItem);
        showNotification('Added to wishlist');
    }

    localStorage.setItem('wishlist', JSON.stringify(wishlist));
    updateWishlistCount();
}

function updateWishlistCount() {
    const wishlist = readItems('wishlist');
    const badges = document.querySelectorAll('#wishlistCount, .icon-link .badge');
    badges.forEach(badge => {
        if (badge.id === 'wishlistCount' || badge.closest('.icon-link').href.includes('wishlist')) {
            badge.textContent = wishlist.length;
        }
    });
}

function showNotification(message) {
    const notification = document.createElement('div');
    notification.className = 'notification';
    notification.textContent = message;
    notification.style.cssText = `
        position: fixed;
        top: 100px;
        right: 20px;
        background: #5C2A2F;
        color: white;
        padding: 15px 25px;
        border-radius: 5px;
        z-index: 10000;
        animation: slideIn 0.3s ease-out;
    `;

    document.body.appendChild(notification);

    setTimeout(() => {
        notification.style.animation = 'slideOut 0.3s ease-out';
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}

// Load products on page load
document.addEventListener('DOMContentLoaded', () => {
    loadBestSellers();
    loadPromoProducts();
    updateWishlistCount();
});

// Export for other files
if (typeof module !== 'undefined' && module.exports) {
    module.exports = { products, formatPrice };
}