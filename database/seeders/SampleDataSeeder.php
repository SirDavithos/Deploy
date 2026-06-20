<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Str;

class SampleDataSeeder extends Seeder
{
    public function run()
    {
        // Limpiar datos anteriores
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('role_user')->truncate();
        DB::table('user_phones')->truncate();
        DB::table('user_addresses')->truncate();
        DB::table('user_tax_data')->truncate();
        DB::table('reviews')->truncate();
        DB::table('order_items')->truncate();
        DB::table('orders')->truncate();
        DB::table('carts')->truncate();
        DB::table('products')->truncate();
        DB::table('shop_tax_data')->truncate();   // <-- nueva tabla
        DB::table('shop_phones')->truncate();
        DB::table('shop_addresses')->truncate();
        DB::table('shops')->truncate();
        DB::table('categories')->truncate();
        DB::table('users')->truncate();
        DB::table('roles')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $now = Carbon::now();
        $passwordHash = '$2y$12$BPV7WHHTBea8Qrx82H4xPO8Lb7chTB78r1ehZMvh39NWvmFAQ0mVK';

        // 1. Roles
        $roles = [
            ['name' => 'Administrador', 'slug' => 'admin',    'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Comprador',     'slug' => 'customer', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Vendedor',      'slug' => 'artisan',  'created_at' => $now, 'updated_at' => $now],
        ];
        DB::table('roles')->insert($roles);
        $roleIds = DB::table('roles')->pluck('id', 'slug');

        // 2. Categorías (10 categorías)
        $categories = [
            ['name' => 'Textiles y Aguayos', 'slug' => 'textiles-aguayos'],
            ['name' => 'Joyería en Plata',   'slug' => 'joyeria-plata'],
            ['name' => 'Cerámica y Arte',    'slug' => 'ceramica-arte'],
            ['name' => 'Cuero Artesanal',    'slug' => 'cuero-artesanal'],
            ['name' => 'Tallados en Madera', 'slug' => 'tallados-madera'],
            ['name' => 'Velas y Aromas',     'slug' => 'velas-aromas'],
            ['name' => 'Manualidades',       'slug' => 'manualidades'],
            ['name' => 'Pinturas y Lienzos', 'slug' => 'pinturas-lienzos'],
            ['name' => 'Instrumentos Nativos','slug' => 'instrumentos-nativos'],
            ['name' => 'Bordados y Tejidos', 'slug' => 'bordados-tejidos'],
        ];
        foreach ($categories as &$cat) {
            $cat['created_at'] = $now;
            $cat['updated_at'] = $now;
        }
        DB::table('categories')->insert($categories);
        $categoryIds = DB::table('categories')->pluck('id', 'slug');

        // 3. Usuarios
        $admins = [
            ['first_name' => 'Carlos', 'paternal_last_name' => 'Quispe', 'maternal_last_name' => 'Mamani', 'ci_number' => '1234567', 'ci_extension' => 'LP', 'birth_date' => '1985-03-15', 'email' => 'carlos.quispe@admin.pb.bo'],
            ['first_name' => 'Mónica', 'paternal_last_name' => 'Flores', 'maternal_last_name' => 'Apaza', 'ci_number' => '2345678', 'ci_extension' => 'LP', 'birth_date' => '1990-07-22', 'email' => 'monica.flores@admin.pb.bo'],
        ];

        // Vendedores especializados (10)
        $vendedores = [
            ['first_name' => 'Juana',   'paternal_last_name' => 'Condori',  'maternal_last_name' => 'Ticona',   'ci_number' => '3456789', 'ci_extension' => 'LP', 'birth_date' => '1980-11-02', 'email' => 'juana.condori@email.com', 'cat_slug' => 'textiles-aguayos'],
            ['first_name' => 'Pedro',   'paternal_last_name' => 'Mamani',   'maternal_last_name' => 'López',    'ci_number' => '4567890', 'ci_extension' => 'LP', 'birth_date' => '1978-05-17', 'email' => 'pedro.mamani@email.com', 'cat_slug' => 'joyeria-plata'],
            ['first_name' => 'Sonia',   'paternal_last_name' => 'Huanaco',  'maternal_last_name' => 'Quisbert', 'ci_number' => '5678901', 'ci_extension' => 'LP', 'birth_date' => '1992-09-09', 'email' => 'sonia.huanaco@email.com', 'cat_slug' => 'ceramica-arte'],
            ['first_name' => 'Rolando', 'paternal_last_name' => 'Ticona',   'maternal_last_name' => 'Luna',     'ci_number' => '6789012', 'ci_extension' => 'LP', 'birth_date' => '1988-12-01', 'email' => 'rolando.ticona@email.com', 'cat_slug' => 'cuero-artesanal'],
            ['first_name' => 'Elena',   'paternal_last_name' => 'Vargas',   'maternal_last_name' => 'Gonzales', 'ci_number' => '7890123', 'ci_extension' => 'LP', 'birth_date' => '1983-06-25', 'email' => 'elena.vargas@email.com', 'cat_slug' => 'tallados-madera'],
            ['first_name' => 'Luis',    'paternal_last_name' => 'Paredes',  'maternal_last_name' => 'Flores',   'ci_number' => '8901234', 'ci_extension' => 'LP', 'birth_date' => '1975-02-14', 'email' => 'luis.paredes@email.com', 'cat_slug' => 'velas-aromas'],
            ['first_name' => 'Ana',     'paternal_last_name' => 'Gutiérrez','maternal_last_name' => 'Castro',   'ci_number' => '9012345', 'ci_extension' => 'LP', 'birth_date' => '1995-04-30', 'email' => 'ana.gutierrez@email.com', 'cat_slug' => 'manualidades'],
            ['first_name' => 'Jorge',   'paternal_last_name' => 'Apaza',    'maternal_last_name' => 'Valdivia', 'ci_number' => '1123456', 'ci_extension' => 'LP', 'birth_date' => '1987-08-12', 'email' => 'jorge.apaza@email.com', 'cat_slug' => 'pinturas-lienzos'],
            ['first_name' => 'Carmen',  'paternal_last_name' => 'Laura',    'maternal_last_name' => 'Vino',     'ci_number' => '2123457', 'ci_extension' => 'LP', 'birth_date' => '1991-01-05', 'email' => 'carmen.laura@email.com', 'cat_slug' => 'instrumentos-nativos'],
            ['first_name' => 'Oscar',   'paternal_last_name' => 'Mendoza',  'maternal_last_name' => 'León',     'ci_number' => '3123458', 'ci_extension' => 'LP', 'birth_date' => '1984-10-18', 'email' => 'oscar.mendoza@email.com', 'cat_slug' => 'bordados-tejidos'],
        ];

        // Compradores (25 personas)
        $compradores = [
            ['first_name' => 'María',     'paternal_last_name' => 'Rodríguez', 'maternal_last_name' => 'Pérez',     'ci_number' => '4123459', 'ci_extension' => 'LP', 'birth_date' => '1993-02-28', 'email' => 'maria.rodriguez@email.com'],
            ['first_name' => 'José',      'paternal_last_name' => 'López',     'maternal_last_name' => 'García',    'ci_number' => '5123460', 'ci_extension' => 'LP', 'birth_date' => '1986-07-14', 'email' => 'jose.lopez@email.com'],
            ['first_name' => 'Rosario',   'paternal_last_name' => 'González',  'maternal_last_name' => 'Fernández', 'ci_number' => '6123461', 'ci_extension' => 'LP', 'birth_date' => '1998-09-03', 'email' => 'rosario.gonzalez@email.com'],
            ['first_name' => 'Mario',     'paternal_last_name' => 'Martínez',  'maternal_last_name' => 'López',     'ci_number' => '7123462', 'ci_extension' => 'LP', 'birth_date' => '1982-11-20', 'email' => 'mario.martinez@email.com'],
            ['first_name' => 'Claudia',   'paternal_last_name' => 'Sánchez',   'maternal_last_name' => 'Ramírez',   'ci_number' => '8123463', 'ci_extension' => 'LP', 'birth_date' => '1990-05-11', 'email' => 'claudia.sanchez@email.com'],
            ['first_name' => 'Miguel',    'paternal_last_name' => 'Díaz',      'maternal_last_name' => 'Torres',    'ci_number' => '9123464', 'ci_extension' => 'LP', 'birth_date' => '1979-03-08', 'email' => 'miguel.diaz@email.com'],
            ['first_name' => 'Sofía',     'paternal_last_name' => 'Herrera',   'maternal_last_name' => 'Ruiz',      'ci_number' => '1123465', 'ci_extension' => 'LP', 'birth_date' => '1996-12-17', 'email' => 'sofia.herrera@email.com'],
            ['first_name' => 'Roberto',   'paternal_last_name' => 'Vargas',    'maternal_last_name' => 'Ponce',     'ci_number' => '2123466', 'ci_extension' => 'LP', 'birth_date' => '1988-01-29', 'email' => 'roberto.vargas@email.com'],
            ['first_name' => 'Angélica',  'paternal_last_name' => 'Morales',   'maternal_last_name' => 'Siles',     'ci_number' => '3123467', 'ci_extension' => 'LP', 'birth_date' => '1994-08-22', 'email' => 'angelica.morales@email.com'],
            ['first_name' => 'Francisco', 'paternal_last_name' => 'Ortiz',     'maternal_last_name' => 'Pizarro',   'ci_number' => '4123468', 'ci_extension' => 'LP', 'birth_date' => '1981-06-07', 'email' => 'francisco.ortiz@email.com'],
            // 15 compradores adicionales
            ['first_name' => 'Patricia',  'paternal_last_name' => 'Rojas',     'maternal_last_name' => 'Mendoza',   'ci_number' => '5123469', 'ci_extension' => 'LP', 'birth_date' => '1991-11-15', 'email' => 'patricia.rojas@email.com'],
            ['first_name' => 'Fernando',  'paternal_last_name' => 'Cruz',      'maternal_last_name' => 'Aliaga',    'ci_number' => '6123470', 'ci_extension' => 'LP', 'birth_date' => '1985-06-21', 'email' => 'fernando.cruz@email.com'],
            ['first_name' => 'Gabriela',  'paternal_last_name' => 'Villanueva','maternal_last_name' => 'Paz',       'ci_number' => '7123471', 'ci_extension' => 'LP', 'birth_date' => '1994-03-09', 'email' => 'gabriela.villanueva@email.com'],
            ['first_name' => 'Hugo',      'paternal_last_name' => 'Ticona',    'maternal_last_name' => 'Quispe',    'ci_number' => '8123472', 'ci_extension' => 'LP', 'birth_date' => '1978-10-12', 'email' => 'hugo.ticona@email.com'],
            ['first_name' => 'Daniela',   'paternal_last_name' => 'Zambrana',  'maternal_last_name' => 'Aruquipa',  'ci_number' => '9123473', 'ci_extension' => 'LP', 'birth_date' => '1999-01-25', 'email' => 'daniela.zambrana@email.com'],
            ['first_name' => 'Andrés',    'paternal_last_name' => 'Chávez',    'maternal_last_name' => 'Torrico',   'ci_number' => '1123474', 'ci_extension' => 'LP', 'birth_date' => '1983-08-17', 'email' => 'andres.chavez@email.com'],
            ['first_name' => 'Natalia',   'paternal_last_name' => 'Guzmán',    'maternal_last_name' => 'Vargas',    'ci_number' => '2123475', 'ci_extension' => 'LP', 'birth_date' => '1996-04-05', 'email' => 'natalia.guzman@email.com'],
            ['first_name' => 'Javier',    'paternal_last_name' => 'Peña',      'maternal_last_name' => 'Miranda',   'ci_number' => '3123476', 'ci_extension' => 'LP', 'birth_date' => '1989-12-30', 'email' => 'javier.pena@email.com'],
            ['first_name' => 'Lucía',     'paternal_last_name' => 'Montaño',   'maternal_last_name' => 'Pari',      'ci_number' => '4123477', 'ci_extension' => 'LP', 'birth_date' => '1995-07-19', 'email' => 'lucia.montano@email.com'],
            ['first_name' => 'Raúl',      'paternal_last_name' => 'Calle',     'maternal_last_name' => 'Condo',     'ci_number' => '5123478', 'ci_extension' => 'LP', 'birth_date' => '1982-02-14', 'email' => 'raul.calle@email.com'],
            ['first_name' => 'Sara',      'paternal_last_name' => 'Tola',      'maternal_last_name' => 'Huanca',    'ci_number' => '6123479', 'ci_extension' => 'LP', 'birth_date' => '1997-09-08', 'email' => 'sara.tola@email.com'],
            ['first_name' => 'Diego',     'paternal_last_name' => 'Paredes',   'maternal_last_name' => 'Luna',      'ci_number' => '7123480', 'ci_extension' => 'LP', 'birth_date' => '1990-06-26', 'email' => 'diego.paredes@email.com'],
            ['first_name' => 'Mónica',    'paternal_last_name' => 'Quisbert',  'maternal_last_name' => 'Flores',    'ci_number' => '8123481', 'ci_extension' => 'LP', 'birth_date' => '1984-11-02', 'email' => 'monica.quisbert@email.com'],
            ['first_name' => 'Juan',      'paternal_last_name' => 'Mamani',    'maternal_last_name' => 'Gutiérrez', 'ci_number' => '9123482', 'ci_extension' => 'LP', 'birth_date' => '1987-04-15', 'email' => 'juan.mamani@email.com'],
            ['first_name' => 'Carla',     'paternal_last_name' => 'Salazar',   'maternal_last_name' => 'Vásquez',   'ci_number' => '1123483', 'ci_extension' => 'LP', 'birth_date' => '1998-10-31', 'email' => 'carla.salazar@email.com'],
        ];

        $allUsers = [
            ['role' => 'admin',    'data' => $admins],
            ['role' => 'artisan',  'data' => $vendedores],
            ['role' => 'customer', 'data' => $compradores],
        ];

        $userId = 1;
        $customerIds = [];
        $approvedShopIds = [];
        $phoneData = [];
        $addressData = [];
        $taxData = [];
        $shopData = [];
        $productData = [];
        $shopPhoneData = [];
        $shopAddressData = [];
        $shopTaxData = [];          // <-- datos fiscales de la tienda
        $reviewData = [];
        $orderData = [];
        $orderItemData = [];

        $zonas = ['Sopocachi', 'Miraflores', 'Centro', 'San Miguel', 'Obrajes', 'Calacoto', 'Villa Fátima', 'El Alto'];
        $calles = [
            'Av. 20 de Octubre', 'Calle Sagárnaga', 'Pasaje Marina Núñez',
            'Av. 6 de Agosto', 'Calle Murillo', 'Av. Arce',
            'Calle Potosí', 'Calle Loayza', 'Av. Villazón'
        ];

        $shopStatuses = ['pending', 'approved', 'approved', 'approved', 'rejected', 'pending', 'approved', 'approved', 'rejected', 'pending'];

        // Productos por categoría (15 títulos sugeridos)
        $productTitles = [
            'textiles-aguayos' => ['Aguayo tradicional', 'Chompa de alpaca', 'Bufanda tejida', 'Poncho andino', 'Falda bordada', 'Manta de vicuña', 'Gorro chullo', 'Tapiz decorativo', 'Camino de mesa', 'Cojín artesanal', 'Capa tejida', 'Chalina', 'Frazada', 'Bolsa de tela', 'Individual'],
            'joyeria-plata'   => ['Anillo de plata 950', 'Collar de filigrana', 'Aretes andinos', 'Pulsera trenzada', 'Dije de luna', 'Prendedor de alpaca', 'Rosario de plata', 'Tumi decorativo', 'Colgante de sol', 'Gargantilla paceña', 'Pulsera de eslabones', 'Aro c/moneda', 'Collar de cuentas', 'Anillo ajustable', 'Pendientes'],
            'ceramica-arte'    => ['Jarrón ceremonial', 'Taza pintada a mano', 'Plato decorativo', 'Vasija de barro', 'Máscara de danza', 'Escultura pequeña', 'Cazuela rústica', 'Alcancía creativa', 'Plato hondo', 'Candelabro de arcilla', 'Figura de llama', 'Maceta pintada', 'Cenicero', 'Tetera artesanal', 'Juego de posavasos'],
            'cuero-artesanal'  => ['Billetera de cuero', 'Monedero', 'Llavero grabado', 'Correa trenzada', 'Porta celular', 'Funda para cuchillo', 'Bandolera pequeña', 'Pulsera de cuero', 'Posavasos', 'Tarjetero', 'Cartuchera', 'Funda para tablet', 'Morral', 'Riñonera', 'Cinturón ancho'],
            'tallados-madera'  => ['Máscara tallada', 'Caja decorativa', 'Cuchara de palo', 'Colgante de madera', 'Figura de animal', 'Crucifijo artesanal', 'Portalápices', 'Tabla para picar', 'Rosetón tallado', 'Adorno de puerta', 'Letrero de madera', 'Juego de ajedrez', 'Porta retrato', 'Candelabro rústico', 'Flauta'],
            'velas-aromas'     => ['Vela aromática de soya', 'Difusor de varillas', 'Vela cónica decorativa', 'Sahumerio artesanal', 'Cera para masajes', 'Vela en tarro', 'Aromatizante en spray', 'Incienso natural', 'Vela flotante', 'Set de velas pequeñas', 'Vela de miel', 'Vela de citronela', 'Porta velas de barro', 'Cera perfumada', 'Vela de cumpleaños'],
            'manualidades'     => ['Atrapasueños', 'Pulsera de hilo', 'Llavero de macramé', 'Porta macetas colgante', 'Figura de origami', 'Tarjeta pop-up', 'Caja de regalo decorada', 'Móvil para bebé', 'Imanes decorativos', 'Porta lápices de lata', 'Guirnalda de tela', 'Adorno navideño', 'Espejo con mosaico', 'Posa ollas', 'Separador de libros'],
            'pinturas-lienzos' => ['Paisaje de La Paz', 'Retrato andino', 'Acuarela del Illimani', 'Cuadro de la calle Sagárnaga', 'Lienzo abstracto', 'Pintura de la virgen', 'Cuadro de la diablada', 'Miniatura enmarcada', 'Arte en técnica mixta', 'Impresión digital', 'Díptico de paisajes', 'Tríptico de flores', 'Cuadro de la morenada', 'Pintura de animales', 'Lámina artística'],
            'instrumentos-nativos' => ['Zampoña', 'Charango pequeño', 'Quena de madera', 'Bombo andino', 'Palo de lluvia', 'Ocarina de cerámica', 'Chajchas (pezuñas)', 'Rondador', 'Pinkillo', 'Tarka', 'Guitarrilla', 'Maraca decorada', 'Tamboril', 'Sonajero', 'Pututu'],
            'bordados-tejidos' => ['Mantel bordado', 'Servilleta decorada', 'Cojín con diseño', 'Tapete de mesa', 'Bordado enmarcado', 'Pañuelo personalizado', 'Camino de altar', 'Bolsa bordada', 'Vestido típico', 'Cartera tejida', 'Sombrero con cinta', 'Guantes tejidos', 'Calcetines de lana', 'Gorro con pompón', 'Bufanda infinita'],
        ];

        // Precios realistas por categoría (rango)
        $priceRanges = [
            'textiles-aguayos' => [50, 350],
            'joyeria-plata' => [80, 400],
            'ceramica-arte' => [30, 250],
            'cuero-artesanal' => [25, 200],
            'tallados-madera' => [40, 300],
            'velas-aromas' => [15, 100],
            'manualidades' => [20, 150],
            'pinturas-lienzos' => [100, 800],
            'instrumentos-nativos' => [50, 500],
            'bordados-tejidos' => [40, 300],
        ];

        // Comentarios para reseñas
        $positiveComments = [
            'Excelente calidad, muy recomendado.',
            'Hermoso trabajo artesanal.',
            'Me encantó, igual a la foto.',
            'Superó mis expectativas.',
            'Muy buen acabado y material.',
            'Precio justo para la calidad.',
            'Llegó rápido y en perfecto estado.',
            'Los detalles son increíbles.',
            'Volveré a comprar sin duda.',
            'Perfecto para regalo.',
            'La textura y colores son hermosos.',
            'Artesanía de primera.',
            'Muy bien empaquetado.',
            'El diseño es auténtico.',
            '100% recomendable.',
        ];
        $neutralComments = [
            'Buen producto, aunque el color no era exactamente como esperaba.',
            'Correcto, pero tardó un poco en llegar.',
            'Está bien por el precio.',
            'Cumple con lo prometido.',
            'Aceptable, aunque podría mejorar el empaque.',
            'Es bonito, pero más pequeño de lo que pensé.',
            'Calidad media, nada fuera de lo común.',
            'Bien, aunque esperaba más por el precio.',
            'Funciona, pero el material se siente frágil.',
            'Regular, no sé si volvería a comprar.',
            'El diseño es lindo, pero los acabados no tanto.',
            'Lo conservo, aunque no fue exactamente lo que esperaba.',
        ];
        $negativeComments = [
            'No me gustó, esperaba algo de mejor calidad.',
            'El producto llegó dañado.',
            'No coincide con la descripción.',
            'Muy caro para lo que es.',
            'No lo recomiendo.',
            'El material se ve barato.',
            'Tardó muchísimo en llegar.',
            'Lo devolví porque no me sirvió.',
            'Los colores se ven distintos a la foto.',
            'Decepcionante, no cumple.',
            'Se rompió a los pocos días.',
            'Mal empaque, llegó golpeado.',
        ];

        // ==================== CREAR USUARIOS, TIENDAS Y PRODUCTOS ====================
        foreach ($allUsers as $roleGroup) {
            $roleSlug = $roleGroup['role'];
            $roleId = $roleIds[$roleSlug];
            foreach ($roleGroup['data'] as $userData) {
                $catSlug = $userData['cat_slug'] ?? null;
                unset($userData['cat_slug']);

                DB::table('users')->insert(array_merge($userData, [
                    'password'          => $passwordHash,
                    'status'            => 'active',
                    'avatar'            => null,
                    'accepted_terms_at' => $now,
                    'last_login_at'     => null,
                    'last_login_ip'     => null,
                    'email_verified_at' => $now,
                    'created_at'        => $now,
                    'updated_at'        => $now,
                ]));

                DB::table('role_user')->insert([
                    'user_id' => $userId,
                    'role_id' => $roleId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

                if ($roleSlug === 'customer') {
                    $customerIds[] = $userId;
                }

                // 3 Teléfonos por usuario
                $phoneData[] = ['user_id' => $userId, 'phone_number' => '7' . rand(1000000, 9999999), 'type' => 'Principal (WhatsApp)', 'created_at' => $now, 'updated_at' => $now];
                $phoneData[] = ['user_id' => $userId, 'phone_number' => '2' . rand(1000000, 9999999), 'type' => 'Fijo Domicilio', 'created_at' => $now, 'updated_at' => $now];
                $phoneData[] = ['user_id' => $userId, 'phone_number' => '6' . rand(1000000, 9999999), 'type' => 'Trabajo', 'created_at' => $now, 'updated_at' => $now];

                // 3 Direcciones por usuario (con GPS)
                for ($i = 0; $i < 3; $i++) {
                    $zona = $zonas[array_rand($zonas)];
                    $calle = $calles[array_rand($calles)] . ' #' . rand(100, 9999);
                    $addressData[] = [
                        'user_id'        => $userId,
                        'city'           => 'La Paz',
                        'zone'           => $zona,
                        'street_address' => $calle,
                        'reference'      => $i === 0 ? 'Cerca de la plaza' : 'Edificio blanco, piso ' . rand(1, 10),
                        'is_default'     => $i === 0,
                        'latitude'       => round(-16.5 + (rand(-500, 500) / 10000), 8),
                        'longitude'      => round(-68.15 + (rand(-500, 500) / 10000), 8),
                        'created_at'     => $now,
                        'updated_at'     => $now,
                    ];
                }

                // 2 Datos fiscales por usuario
                $razon1 = $roleSlug === 'artisan' ? 'Artesanías ' . $userData['paternal_last_name'] : $userData['first_name'] . ' ' . $userData['paternal_last_name'];
                $razon2 = $roleSlug === 'artisan' ? 'Taller ' . $userData['paternal_last_name'] : $userData['first_name'] . ' ' . $userData['paternal_last_name'];
                $taxData[] = [
                    'user_id'       => $userId,
                    'nit_or_ci'     => $userData['ci_number'] . '001',
                    'business_name' => $razon1,
                    'tax_regimen'   => 'Régimen General',
                    'alias'         => 'Personal',
                    'is_default'    => true,
                    'created_at'    => $now,
                    'updated_at'    => $now,
                ];
                $taxData[] = [
                    'user_id'       => $userId,
                    'nit_or_ci'     => $userData['ci_number'] . '002',
                    'business_name' => $razon2,
                    'tax_regimen'   => 'Simplificado',
                    'alias'         => 'Secundario',
                    'is_default'    => false,
                    'created_at'    => $now,
                    'updated_at'    => $now,
                ];

                // Si es artesano, crear tienda y productos
                if ($roleSlug === 'artisan' && $catSlug) {
                    $status = $shopStatuses[count($shopData) % count($shopStatuses)];
                    $shopName = str_replace('_', ' ', ucfirst($catSlug)) . ' ' . $userData['paternal_last_name'];
                    $slug = Str::slug($shopName) . '-' . $userId;
                    $shopId = count($shopData) + 1;

                    // La mitad de las tiendas serán is_tax_registered
                    $isTaxRegistered = (count($shopData) % 2 === 0);

                    $shopData[] = [
                        'id'                => $shopId,
                        'user_id'           => $userId,
                        'name'              => $shopName,
                        'slug'              => $slug,
                        'description'       => 'Tienda especializada en ' . DB::table('categories')->where('slug', $catSlug)->value('name'),
                        'status'            => $status,
                        'is_tax_registered' => $isTaxRegistered,
                        'created_at'        => $now,
                        'updated_at'        => $now,
                    ];

                    if ($status === 'approved') {
                        $approvedShopIds[] = $shopId;
                    }

                    // 2 Teléfonos para la tienda
                    $shopPhoneData[] = ['shop_id' => $shopId, 'phone_number' => '7' . rand(1000000, 9999999), 'type' => 'Principal', 'created_at' => $now, 'updated_at' => $now];
                    $shopPhoneData[] = ['shop_id' => $shopId, 'phone_number' => '2' . rand(1000000, 9999999), 'type' => 'Fijo', 'created_at' => $now, 'updated_at' => $now];

                    // 1 Dirección para la tienda (CON GPS)
                    $zona = $zonas[array_rand($zonas)];
                    $calle = $calles[array_rand($calles)] . ' #' . rand(100, 9999);
                    $shopAddressData[] = [
                        'shop_id'        => $shopId,
                        'city'           => 'La Paz',
                        'zone'           => $zona,
                        'street_address' => $calle,
                        'reference'      => 'Local ' . rand(1, 50),
                        'is_default'     => true,
                        'latitude'       => round(-16.5 + (rand(-500, 500) / 10000), 8),
                        'longitude'      => round(-68.15 + (rand(-500, 500) / 10000), 8),
                        'created_at'     => $now,
                        'updated_at'     => $now,
                    ];

                    // Datos fiscales para la tienda si es is_tax_registered
                    if ($isTaxRegistered) {
                        $shopTaxData[] = [
                            'shop_id'       => $shopId,
                            'nit_or_ci'     => $userData['ci_number'] . '003',
                            'business_name' => 'Artesanías ' . $userData['paternal_last_name'] . ' S.R.L.',
                            'tax_regimen'   => 'Régimen General',
                            'alias'         => 'Tienda',
                            'is_default'    => true,
                            'created_at'    => $now,
                            'updated_at'    => $now,
                        ];
                    }

                    // 15 Productos por tienda, de su categoría
                    $titles = $productTitles[$catSlug];
                    $prices = $priceRanges[$catSlug];
                    for ($p = 0; $p < 15; $p++) {
                        $title = $titles[$p % count($titles)];
                        $productData[] = [
                            'shop_id'     => $shopId,
                            'user_id'     => $userId,
                            'category_id' => $categoryIds[$catSlug],
                            'title'       => $title,
                            'description' => $title . ' elaborado a mano en La Paz, Bolivia.',
                            'price'       => rand($prices[0], $prices[1]) + (rand(0, 99) / 100),
                            'stock'       => rand(1, 20),
                            'status'      => 'published',
                            'images'      => json_encode([]),
                            'created_at'  => $now,
                            'updated_at'  => $now,
                        ];
                    }
                }

                $userId++;
            }
        }

        // Insertar todos los datos
        DB::table('user_phones')->insert($phoneData);
        DB::table('user_addresses')->insert($addressData);
        DB::table('user_tax_data')->insert($taxData);
        DB::table('shops')->insert($shopData);
        DB::table('shop_phones')->insert($shopPhoneData);
        DB::table('shop_addresses')->insert($shopAddressData);
        DB::table('shop_tax_data')->insert($shopTaxData);   // <-- nueva inserción
        DB::table('products')->insert($productData);

        // Obtener IDs de productos por tienda
        $productIdsByShop = [];
        foreach (DB::table('products')->get() as $prod) {
            $productIdsByShop[$prod->shop_id][] = $prod->id;
        }

        // ==================== RESEÑAS ====================
        foreach ($approvedShopIds as $shopId) {
            $shopProductIds = $productIdsByShop[$shopId] ?? [];
            if (empty($shopProductIds)) continue;

            // Elegir solo algunos productos para reseñar (no todos, para hacerlo más realista)
            $reviewProductIds = array_slice($shopProductIds, 0, 5);

            foreach ($reviewProductIds as $productId) {
                // No todos los compradores reseñan cada producto
                $chosenCustomers = array_rand(array_flip($customerIds), rand(5, count($customerIds)));
                if (!is_array($chosenCustomers)) $chosenCustomers = [$chosenCustomers];

                foreach ($chosenCustomers as $custId) {
                    $rating = rand(1, 5);
                    if ($rating >= 4) {
                        $comment = $positiveComments[array_rand($positiveComments)];
                    } elseif ($rating >= 2) {
                        $comment = $neutralComments[array_rand($neutralComments)];
                    } else {
                        $comment = $negativeComments[array_rand($negativeComments)];
                    }
                    $reviewData[] = [
                        'product_id' => $productId,
                        'user_id'    => $custId,
                        'rating'     => $rating,
                        'comment'    => $comment,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
            }
        }
        DB::table('reviews')->insert($reviewData);

        // ==================== PEDIDOS ====================
        $orderStatuses = ['pending', 'confirmed', 'shipped', 'delivered', 'cancelled'];
        $orderId = 1;

        foreach ($customerIds as $custId) {
            $custAddressIds = DB::table('user_addresses')->where('user_id', $custId)->pluck('id')->toArray();
            $custTaxIds = DB::table('user_tax_data')->where('user_id', $custId)->pluck('id')->toArray();

            // Cada comprador hace entre 3 y 8 pedidos
            $numOrders = rand(3, 8);
            for ($i = 0; $i < $numOrders; $i++) {
                $shopId = $approvedShopIds[array_rand($approvedShopIds)];
                $shopProductIds = $productIdsByShop[$shopId] ?? [];
                if (empty($shopProductIds)) continue;

                $numItems = rand(1, 2);
                $orderProductIds = array_rand(array_flip($shopProductIds), $numItems);
                if (!is_array($orderProductIds)) $orderProductIds = [$orderProductIds];

                $total = 0;
                $items = [];
                foreach ($orderProductIds as $productId) {
                    $product = DB::table('products')->where('id', $productId)->first();
                    if (!$product) continue;
                    $quantity = rand(1, 3);
                    $itemTotal = $product->price * $quantity;
                    $total += $itemTotal;
                    $items[] = ['product_id' => $productId, 'price' => $product->price, 'quantity' => $quantity];
                }
                if (empty($items)) continue;

                $status = $orderStatuses[array_rand($orderStatuses)];
                $addressId = $custAddressIds[array_rand($custAddressIds)];
                $taxDataId = $custTaxIds[array_rand($custTaxIds)];

                $orderData[] = [
                    'id'          => $orderId,
                    'user_id'     => $custId,
                    'shop_id'     => $shopId,
                    'address_id'  => $addressId,
                    'tax_data_id' => $taxDataId,
                    'status'      => $status,
                    'total'       => round($total, 2),
                    'created_at'  => $now,
                    'updated_at'  => $now,
                ];

                foreach ($items as $item) {
                    $orderItemData[] = [
                        'order_id'   => $orderId,
                        'product_id' => $item['product_id'],
                        'quantity'   => $item['quantity'],
                        'price'      => $item['price'],
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
                $orderId++;
            }
        }
        DB::table('orders')->insert($orderData);
        DB::table('order_items')->insert($orderItemData);

        $this->command->info("Seeder ejecutado: " . ($userId-1) . " usuarios, " . count($shopData) . " tiendas, " . count($productData) . " productos, " . count($reviewData) . " reseñas, " . count($orderData) . " pedidos.");
    }
}