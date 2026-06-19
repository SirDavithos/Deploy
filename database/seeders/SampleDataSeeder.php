<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

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
        DB::table('products')->truncate();
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

        // 2. Categorías (5 categorías)
        $categories = [
            ['name' => 'Textiles y Aguayos', 'slug' => 'textiles-aguayos'],
            ['name' => 'Joyería en Plata',   'slug' => 'joyeria-plata'],
            ['name' => 'Cerámica y Arte',    'slug' => 'ceramica-arte'],
            ['name' => 'Cuero Artesanal',    'slug' => 'cuero-artesanal'],
            ['name' => 'Tallados en Madera', 'slug' => 'tallados-madera'],
        ];
        foreach ($categories as &$cat) {
            $cat['created_at'] = $now;
            $cat['updated_at'] = $now;
        }
        DB::table('categories')->insert($categories);
        $categoryIds = DB::table('categories')->pluck('id');

        // 3. Usuarios
        $admins = [
            ['first_name' => 'Carlos', 'paternal_last_name' => 'Quispe', 'maternal_last_name' => 'Mamani', 'ci_number' => '1234567', 'ci_extension' => 'LP', 'birth_date' => '1985-03-15', 'email' => 'carlos.quispe@admin.pb.bo'],
            ['first_name' => 'Mónica', 'paternal_last_name' => 'Flores', 'maternal_last_name' => 'Apaza', 'ci_number' => '2345678', 'ci_extension' => 'LP', 'birth_date' => '1990-07-22', 'email' => 'monica.flores@admin.pb.bo'],
        ];

        $vendedores = [
            ['first_name' => 'Juana',   'paternal_last_name' => 'Condori',  'maternal_last_name' => 'Ticona',   'ci_number' => '3456789', 'ci_extension' => 'LP', 'birth_date' => '1980-11-02', 'email' => 'juana.condori@email.com'],
            ['first_name' => 'Pedro',   'paternal_last_name' => 'Mamani',   'maternal_last_name' => 'López',    'ci_number' => '4567890', 'ci_extension' => 'LP', 'birth_date' => '1978-05-17', 'email' => 'pedro.mamani@email.com'],
            ['first_name' => 'Sonia',   'paternal_last_name' => 'Huanaco',  'maternal_last_name' => 'Quisbert', 'ci_number' => '5678901', 'ci_extension' => 'LP', 'birth_date' => '1992-09-09', 'email' => 'sonia.huanaco@email.com'],
            ['first_name' => 'Rolando', 'paternal_last_name' => 'Ticona',   'maternal_last_name' => 'Luna',     'ci_number' => '6789012', 'ci_extension' => 'LP', 'birth_date' => '1988-12-01', 'email' => 'rolando.ticona@email.com'],
            ['first_name' => 'Elena',   'paternal_last_name' => 'Vargas',   'maternal_last_name' => 'Gonzales', 'ci_number' => '7890123', 'ci_extension' => 'LP', 'birth_date' => '1983-06-25', 'email' => 'elena.vargas@email.com'],
            ['first_name' => 'Luis',    'paternal_last_name' => 'Paredes',  'maternal_last_name' => 'Flores',   'ci_number' => '8901234', 'ci_extension' => 'LP', 'birth_date' => '1975-02-14', 'email' => 'luis.paredes@email.com'],
            ['first_name' => 'Ana',     'paternal_last_name' => 'Gutiérrez','maternal_last_name' => 'Castro',   'ci_number' => '9012345', 'ci_extension' => 'LP', 'birth_date' => '1995-04-30', 'email' => 'ana.gutierrez@email.com'],
            ['first_name' => 'Jorge',   'paternal_last_name' => 'Apaza',    'maternal_last_name' => 'Valdivia', 'ci_number' => '1123456', 'ci_extension' => 'LP', 'birth_date' => '1987-08-12', 'email' => 'jorge.apaza@email.com'],
            ['first_name' => 'Carmen',  'paternal_last_name' => 'Laura',    'maternal_last_name' => 'Vino',     'ci_number' => '2123457', 'ci_extension' => 'LP', 'birth_date' => '1991-01-05', 'email' => 'carmen.laura@email.com'],
            ['first_name' => 'Oscar',   'paternal_last_name' => 'Mendoza',  'maternal_last_name' => 'León',     'ci_number' => '3123458', 'ci_extension' => 'LP', 'birth_date' => '1984-10-18', 'email' => 'oscar.mendoza@email.com'],
        ];

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
        ];

        $allUsers = [
            ['role' => 'admin',    'data' => $admins],
            ['role' => 'artisan',  'data' => $vendedores],
            ['role' => 'customer', 'data' => $compradores],
        ];

        $userId = 1;
        $phoneData = [];
        $addressData = [];
        $taxData = [];
        $shopData = [];
        $productData = [];

        $zonas = ['Sopocachi', 'Miraflores', 'Centro', 'San Miguel', 'Obrajes', 'Calacoto', 'Villa Fátima', 'El Alto'];
        $calles = [
            'Av. 20 de Octubre', 'Calle Sagárnaga', 'Pasaje Marina Núñez',
            'Av. 6 de Agosto', 'Calle Murillo', 'Av. Arce',
            'Calle Potosí', 'Calle Loayza', 'Av. Villazón'
        ];

        // Estados de tiendas para variar
        $shopStatuses = ['pending', 'approved', 'approved', 'approved', 'rejected', 'pending', 'approved', 'approved', 'rejected', 'pending'];

        // Nombres de tiendas sugeridos (uno por artesano)
        $shopNames = [
            'Artesanías Condori',
            'Mamani Tejidos',
            'Huanaco Platería',
            'Taller Ticona',
            'Elena Bordados',
            'Paredes Cuero',
            'Ana Cerámicas',
            'Apaza Maderas',
            'Carmen Arte Textil',
            'Mendoza Instrumentos',
        ];

        // Datos para productos (10 por tienda)
        $productTitles = [
            'Textiles y Aguayos' => ['Aguayo tradicional', 'Chompa de alpaca', 'Bufanda tejida', 'Poncho andino', 'Falda bordada', 'Manta de vicuña', 'Gorro chullo', 'Tapiz decorativo', 'Camino de mesa', 'Cojín artesanal'],
            'Joyería en Plata'   => ['Anillo de plata 950', 'Collar de filigrana', 'Aretes andinos', 'Pulsera trenzada', 'Dije de luna', 'Prendedor de alpaca', 'Rosario de plata', 'Tumi decorativo', 'Colgante de sol', 'Gargantilla paceña'],
            'Cerámica y Arte'    => ['Jarrón ceremonial', 'Taza pintada a mano', 'Plato decorativo', 'Vasija de barro', 'Máscara de danza', 'Escultura pequeña', 'Cazuela rústica', 'Alcancía creativa', 'Plato hondo', 'Candelabro de arcilla'],
            'Cuero Artesanal'    => ['Billetera de cuero', 'Monedero', 'Llavero grabado', 'Correa trenzada', 'Porta celular', 'Funda para cuchillo', 'Bandolera pequeña', 'Pulsera de cuero', 'Posavasos', 'Tarjetero'],
            'Tallados en Madera' => ['Máscara tallada', 'Caja decorativa', 'Cuchara de palo', 'Colgante de madera', 'Figura de animal', 'Crucifijo artesanal', 'Portalápices', 'Tabla para picar', 'Rosetón tallado', 'Adorno de puerta'],
        ];

        foreach ($allUsers as $roleGroup) {
            $roleSlug = $roleGroup['role'];
            $roleId = $roleIds[$roleSlug];
            foreach ($roleGroup['data'] as $index => $userData) {
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

                // Teléfonos
                $phoneData[] = [
                    'user_id'      => $userId,
                    'phone_number' => '7' . rand(1000000, 9999999),
                    'type'         => 'Principal (WhatsApp)',
                    'created_at'   => $now, 'updated_at' => $now,
                ];
                $phoneData[] = [
                    'user_id'      => $userId,
                    'phone_number' => '2' . rand(1000000, 9999999),
                    'type'         => 'Fijo Domicilio',
                    'created_at'   => $now, 'updated_at' => $now,
                ];

                // Direcciones
                for ($i = 0; $i < 2; $i++) {
                    $zona = $zonas[array_rand($zonas)];
                    $calle = $calles[array_rand($calles)] . ' #' . rand(100, 9999);
                    $addressData[] = [
                        'user_id'        => $userId,
                        'city'           => 'La Paz',
                        'zone'           => $zona,
                        'street_address' => $calle,
                        'reference'      => ($i === 0) ? 'Cerca de la plaza principal' : 'Edificio blanco, piso ' . rand(1, 10),
                        'latitude'       => round(-16.5 + (rand(-500, 500) / 10000), 8),
                        'longitude'      => round(-68.15 + (rand(-500, 500) / 10000), 8),
                        'is_default'     => ($i === 0),
                        'created_at'     => $now,
                        'updated_at'     => $now,
                    ];
                }

                // Datos fiscales
                $razon1 = ($roleSlug === 'artisan') ? 'Artesanías ' . $userData['paternal_last_name'] : $userData['first_name'] . ' ' . $userData['paternal_last_name'];
                $razon2 = ($roleSlug === 'artisan') ? 'Taller ' . $userData['paternal_last_name'] : $userData['first_name'] . ' ' . $userData['paternal_last_name'];
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
                if ($roleSlug === 'artisan') {
                    $status = $shopStatuses[count($shopData) % count($shopStatuses)];
                    $shopName = $shopNames[count($shopData)];
                    $slug = \Illuminate\Support\Str::slug($shopName) . '-' . $userId;
                    $shopId = count($shopData) + 1; // IDs incrementales

                    $shopData[] = [
                        'id'          => $shopId,
                        'user_id'     => $userId,
                        'name'        => $shopName,
                        'slug'        => $slug,
                        'description' => 'Tienda de artesanías ' . $shopName,
                        'status'      => $status,
                        'created_at'  => $now,
                        'updated_at'  => $now,
                    ];

                    // Productos para esta tienda (10 productos)
                    $catIds = $categoryIds->toArray();
                    foreach ($catIds as $catIndex => $catId) {
                        $catName = DB::table('categories')->where('id', $catId)->value('name');
                        $titles = $productTitles[$catName] ?? ['Producto artesanal'];
                        for ($j = 0; $j < 2; $j++) { // 2 productos por categoría = 10 productos
                            $title = $titles[(($catIndex * 2) + $j) % count($titles)];
                            $productData[] = [
                                'shop_id'     => $shopId,
                                'user_id'     => $userId,
                                'category_id' => $catId,
                                'title'       => $title,
                                'description' => $title . ' elaborado a mano en La Paz, Bolivia.',
                                'price'       => rand(30, 800) + (rand(0, 99) / 100),
                                'stock'       => rand(1, 20),
                                'status'      => 'published',
                                'images'      => json_encode([]),
                                'created_at'  => $now,
                                'updated_at'  => $now,
                            ];
                        }
                    }
                }

                $userId++;
            }
        }

        DB::table('user_phones')->insert($phoneData);
        DB::table('user_addresses')->insert($addressData);
        DB::table('user_tax_data')->insert($taxData);
        DB::table('shops')->insert($shopData);
        DB::table('products')->insert($productData);

        $this->command->info("✅ Seeder ejecutado: {$userId} usuarios, " . count($shopData) . " tiendas, " . count($productData) . " productos generados.");
    }
}