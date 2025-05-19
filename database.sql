-- Drop existing tables if they exist
DROP TABLE IF EXISTS stock_adjustments;
DROP TABLE IF EXISTS order_items;
DROP TABLE IF EXISTS orders;
DROP TABLE IF EXISTS cart_items;
DROP TABLE IF EXISTS carts;
DROP TABLE IF EXISTS reviews;
DROP TABLE IF EXISTS products;
DROP TABLE IF EXISTS categories;
DROP TABLE IF EXISTS brands;
DROP TABLE IF EXISTS users;

-- Create Users table
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(255) NOT NULL UNIQUE,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    name VARCHAR(255),
    address TEXT,
    phone VARCHAR(20),
    is_admin BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Create Brands table
CREATE TABLE brands (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    description TEXT,
    image_url VARCHAR(255),
    founding_year INT,
    headquarters VARCHAR(255),
    website_url VARCHAR(255),
    is_featured BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Create Categories table
CREATE TABLE categories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    description TEXT,
    image_url VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Create Products table
CREATE TABLE products (
    id INT PRIMARY KEY AUTO_INCREMENT,
    brand_id INT NOT NULL,
    category_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    stock INT DEFAULT 100,
    image_url VARCHAR(255),
    alcohol_content DECIMAL(4,2),
    container_type VARCHAR(50) DEFAULT 'Bottle',
    volume VARCHAR(50),
    is_featured BOOLEAN DEFAULT FALSE,
    rating INT DEFAULT 0,
    quantity INT DEFAULT 100,
    quantity_sold INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (brand_id) REFERENCES brands(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
);

-- Create Reviews table
CREATE TABLE reviews (
    id INT PRIMARY KEY AUTO_INCREMENT,
    product_id INT NOT NULL,
    user_id INT NOT NULL,
    rating INT NOT NULL CHECK (rating BETWEEN 1 AND 5),
    comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Create Carts table
CREATE TABLE carts (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Create Cart Items table
CREATE TABLE cart_items (
    id INT PRIMARY KEY AUTO_INCREMENT,
    cart_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (cart_id) REFERENCES carts(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- Create Orders table
CREATE TABLE orders (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'processing', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',
    shipping_address TEXT NOT NULL,
    payment_method VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Create Order Items table
CREATE TABLE order_items (
    id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- Create Stock Adjustments table
CREATE TABLE stock_adjustments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    action ENUM('add', 'subtract') NOT NULL,
    reason TEXT NOT NULL,
    adjusted_by INT NOT NULL,
    adjusted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (adjusted_by) REFERENCES users(id)
);

-- Create Payment Requests table
CREATE TABLE payment_requests (
    id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT NOT NULL,
    payment_request_id VARCHAR(255) NOT NULL,
    payment_id VARCHAR(255) DEFAULT NULL,
    amount DECIMAL(10,2) NOT NULL,
    status ENUM('created', 'completed', 'failed') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
);

-- Insert sample admin user
INSERT INTO users (username, email, password, name, is_admin) VALUES
('admin', 'admin@drunkies.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin User', TRUE);

-- Insert brands data
INSERT INTO brands (id, name, slug, description, image_url, founding_year, headquarters, website_url, created_at, updated_at) VALUES
(1, 'San Miguel Brewery', 'san-miguel-brewery', 'San Miguel Brewery Inc. is the largest beer producer in the Philippines, known for iconic brands like San Miguel Pale Pilsen and Red Horse. Established in 1890, it dominates the local beer market with a wide range of alcoholic beverages.', 'assets/images/brands/san-miguel-brewery.jpg', 1890, 'Mandaluyong, Philippines', NULL, '2025-05-17 02:36:43', '2025-05-17 02:36:43'),
(2, 'Asia Brewery', 'asia-brewery', 'Asia Brewery is one of the largest beverage producers in the Philippines, competing with San Miguel. Known for brands like Beer na Beer and Colt 45, it offers affordable alternatives in the local market.', 'assets/images/brands/asia-brewery.png', 1982, 'Manila, Philippines', NULL, '2025-05-17 02:36:43', '2025-05-17 02:36:43'),
(3, 'Engkanto Brewery', 'engkanto-brewery', 'Engkanto Brewery is a craft brewery in the Philippines known for its high-quality craft beers like Engkanto Pale Ale and IPA. It represents the growing craft beer movement in the country.', 'assets/images/brands/engkanto-brewery.png', 2017, 'Makati, Philippines', NULL, '2025-05-17 02:36:43', '2025-05-17 02:36:43'),
(4, 'The Cebruery', 'the-cebruery', 'The Cebruery is a craft brewery based in Cebu, Philippines, specializing in unique craft beers with tropical flavors. Known for Island Hopper IPA and other innovative brews.', 'assets/images/brands/the-cebruery.jpg', 2016, 'Cebu, Philippines', NULL, '2025-05-17 02:36:43', '2025-05-17 02:36:43'),
(5, 'Pedro Brewcrafters', 'pedro-brewcrafters', 'Pedro Brewcrafters is a Filipino craft brewery known for its creative beer offerings. Based in Manila, it produces small-batch craft beers with local ingredients and innovative recipes.', 'assets/images/brands/pedro-brewcrafters.jpg', 2015, 'Manila, Philippines', NULL, '2025-05-17 02:36:43', '2025-05-17 02:36:43'),
(6, 'Crazy Carabao', 'crazy-carabao', 'Crazy Carabao is a local craft brewery producing unique Filipino-inspired craft beers. Known for its playful branding and quality brews that appeal to both casual drinkers and beer enthusiasts.', 'assets/images/brands/crazy-carabao.png', 2018, 'Quezon City, Philippines', NULL, '2025-05-17 02:36:43', '2025-05-17 02:36:43'),
(7, 'Nipa Brew', 'nipa-brew', 'Nipa Brew is a Filipino craft beer brand specializing in locally-inspired brews. The name references the traditional nipa palm, symbolizing their commitment to Filipino flavors and ingredients.', 'assets/images/brands/nipa-brew.png', 2019, 'Manila, Philippines', NULL, '2025-05-17 02:36:44', '2025-05-17 02:36:44'),
(8, 'Alamat Brewery', 'alamat-brewery', 'Alamat Brewery crafts beers inspired by Filipino culture and ingredients. Known for innovative brews like their Ube Stout, they celebrate local flavors through craft beer.', 'assets/images/brands/alamat-brewery.jpg', 2020, 'Taguig, Philippines', NULL, '2025-05-17 02:36:44', '2025-05-17 02:36:44'),
(9, 'Joe\'s Brew', 'joes-brew', 'Joe\'s Brew is a pioneer in the Philippine craft beer scene, offering a range of artisanal beers since 2011. Known for their Fish Rider Pale Ale and commitment to quality brewing.', 'assets/images/brands/joes-brew.jpg', 2011, 'Manila, Philippines', NULL, '2025-05-17 02:36:44', '2025-05-17 02:36:44'),
(10, 'Fat Pauly\'s Craft Brewery', 'fat-paulys-craft-brewery', 'Fat Pauly\'s Craft Brewery specializes in bold, flavorful craft beers with a Filipino twist. Their IPA and Coffee Porter are particularly popular among craft beer enthusiasts.', 'assets/images/brands/fat-paulys-craft-brewery.jpg', 2017, 'Cebu, Philippines', NULL, '2025-05-17 02:36:44', '2025-05-17 02:36:44');

-- Insert categories data
INSERT INTO categories (id, name, slug, description, image_url, created_at, updated_at) VALUES
(1, 'Lagers', 'lagers', 'Crisp, clean, and refreshing beers fermented with bottom-fermenting yeast at cooler temperatures.', 'images/categories/lagers/lagers.jpg', '2025-05-17 23:17:13', '2025-05-17 23:17:13'),
(2, 'Ales', 'ales', 'Full-bodied beers fermented with top-fermenting yeast at warmer temperatures, offering complex flavors.', 'images/categories/ales/ales.jpg', '2025-05-17 23:17:13', '2025-05-17 23:17:13'),
(3, 'Wheat Beers', 'wheat-beers', 'Light, refreshing beers brewed with a significant proportion of wheat relative to malted barley.', 'images/categories/wheat-beers/wheat-beers.jpg', '2025-05-17 23:17:13', '2025-05-17 23:17:13'),
(4, 'Stouts', 'stouts', 'Dark, rich beers made using roasted malt or barley, known for their coffee and chocolate notes.', 'images/categories/stouts/stouts.jpg', '2025-05-17 23:17:13', '2025-05-17 23:17:13'),
(5, 'Porters', 'porters', 'Dark beers with a rich history, featuring roasted malt flavors with chocolate and caramel notes.', 'images/categories/porters/porters.jpg', '2025-05-17 23:17:13', '2025-05-17 23:17:13'),
(6, 'Craft', 'craft', 'Unique and innovative beers from small, independent breweries, often experimenting with flavors.', 'images/categories/craft/craft.jpg', '2025-05-17 23:17:13', '2025-05-17 23:17:13');

-- Insert products data
INSERT INTO products (id, brand_id, category_id, name, slug, description, price, stock, image_url, alcohol_content, container_type, volume, is_featured, rating, created_at, updated_at, quantity, quantity_sold) VALUES
(1, 1, 1, 'San Miguel Pale Pilsen', 'san-miguel-pale-pilsen', 'San Miguel Pale Pilsen is the iconic Filipino pale lager known for its crisp, well-balanced flavor and slightly bitter finish. It has been a cultural staple for over a century, enjoyed across all social settings. This beer pairs excellently with a wide variety of local dishes.', 419.00, 100, 'assets/images/products/san-miguel-pale-pilsen.png', 5.00, 'Can', '330ml', 1, 5, '2025-05-17 23:17:13', '2025-05-17 23:17:13', 100, 0),
(2, 1, 1, 'Red Horse Beer', 'red-horse-beer', 'Red Horse Beer is a bold, strong lager with a full-bodied malt character and higher alcohol content, favored for its intense flavor. It delivers a smooth yet powerful drinking experience, perfect for those who prefer a stronger beer. It is often consumed during festive and social occasions.', 369.00, 100, 'assets/images/products/redhorse-beer.jpg', 6.90, 'Can', '330ml', 1, 5, '2025-05-17 23:17:13', '2025-05-17 23:17:13', 100, 0),
(3, 1, 1, 'San Mig Light', 'san-mig-light', 'A low-calorie, light lager offering a smooth and easy-drinking profile with subtle malt sweetness.', 369.00, 100, 'assets/images/products/san-mig-light.jpg', 4.50, 'Can', '330ml', 0, 3, '2025-05-17 23:17:13', '2025-05-17 23:17:13', 100, 0),
(4, 1, 1, 'Gold Eagle Beer', 'gold-eagle-beer', 'A traditional lager with a slightly sweet malt character and a clean, crisp finish.', 375.00, 100, 'assets/images/products/gold-eagle-beer.jpg', 5.00, 'Bottle', '330ml', 0, 4, '2025-05-17 23:17:13', '2025-05-17 23:17:13', 100, 0),
(5, 1, 1, 'Cerveza Negra', 'cerveza-negra', 'A dark lager featuring rich caramel and roasted malt flavors with hints of coffee and chocolate.', 474.00, 100, 'assets/images/products/cerveza-negra.jpg', 5.30, 'Bottle', '330ml', 0, 4, '2025-05-17 23:17:13', '2025-05-17 23:17:13', 100, 0),
(6, 1, 1, 'San Miguel Super Dry', 'san-miguel-super-dry', 'A crisp, dry lager brewed for a clean and refreshing taste with a light body.', 462.50, 100, 'assets/images/products/san-miguel-super-dry.jpg', 5.00, 'Can', '330ml', 0, 3, '2025-05-17 23:17:13', '2025-05-17 23:17:13', 100, 0),
(7, 1, 1, 'San Miguel Premium All Malt Beer', 'san-miguel-premium-all-malt-beer', 'A premium all-malt lager brewed with 100% malt, offering a richer and more robust malt flavor.', 474.75, 100, 'assets/images/products/san-miguel-premium.png', 5.50, 'Bottle', '330ml', 0, 4, '2025-05-17 23:17:13', '2025-05-17 23:17:13', 100, 0),
(8, 1, 1, 'San Mig Zero', 'san-mig-zero', 'A non-alcoholic beer that retains the classic San Miguel Pale Pilsen taste without the alcohol.', 375.00, 100, 'assets/images/products/san-mig-zero.jpg', 0.00, 'Can', '330ml', 0, 3, '2025-05-17 23:17:13', '2025-05-17 23:17:13', 100, 0),
(9, 1, 1, 'Red Horse Super', 'red-horse-super', 'An extra-strong variant of the classic Red Horse, with higher alcohol content and a more intense malt profile.', 425.00, 100, 'assets/images/products/red-horse-super.png', 7.50, 'Can', '330ml', 0, 4, '2025-05-17 23:17:13', '2025-05-17 23:17:13', 100, 0),
(10, 1, 4, 'San Miguel Strong Ice', 'san-miguel-strong-ice', 'San Miguel Strong Ice is a malt liquor with stout-like characteristics, combining a strong alcohol kick with a creamy, smooth mouthfeel. It blends the richness of stout with the refreshing qualities of malt liquor. This beer is popular among drinkers seeking variety and strength.', 375.00, 100, 'assets/images/products/san-miguel-strong-ice.png', 7.50, 'Can', '330ml', 0, 4, '2025-05-17 23:17:13', '2025-05-17 23:17:13', 100, 0),
(11, 1, 2, 'San Miguel Apple Ale', 'san-miguel-apple-ale', 'A fruit ale that infuses crisp apple flavors into a light ale base.', 354.00, 100, 'assets/images/products/san-miguel-apple-ale.jpeg', 4.20, 'Can', '330ml', 0, 3, '2025-05-17 23:17:13', '2025-05-17 23:17:13', 100, 0),
(12, 1, 3, 'San Miguel Flavored Beer Lemon', 'san-miguel-flavored-beer-lemon', 'A wheat ale with zesty lemon notes, creating a refreshing and aromatic drink.', 419.00, 100, 'assets/images/products/san-miguel-lemon.png', 4.00, 'Can', '330ml', 0, 4, '2025-05-17 23:17:13', '2025-05-17 23:17:13', 100, 0),
(13, 2, 1, 'Beer na Beer', 'beer-na-beer', 'Beer na Beer is a classic pale pilsen with a clean, crisp taste and mild hop bitterness. It is an easy-to-drink lager that has been a long-time favorite in the Philippine market. This beer pairs well with many Filipino dishes.', 375.00, 100, 'assets/images/products/beer-na-beer.jpg', 5.00, 'Bottle', '330ml', 1, 4, '2025-05-17 23:17:13', '2025-05-17 23:17:13', 100, 0),
(14, 2, 1, 'Colt 45', 'colt-45', 'Colt 45 is a malt liquor with higher alcohol content and a smooth, slightly sweet malt flavor. Known for its strong kick, it appeals to drinkers seeking a more potent beer. The full-bodied taste balances strength with drinkability.', 380.00, 100, 'assets/images/products/colt-45.jpg', 7.00, 'Bottle', '330ml', 0, 4, '2025-05-17 23:17:13', '2025-05-17 23:17:13', 100, 0),
(15, 2, 1, 'Lone Star', 'lone-star', 'A refreshing lager with a light malt character and subtle hop bitterness.', 345.00, 100, 'assets/images/products/lone-star.jpg', 5.00, 'Bottle', '330ml', 0, 3, '2025-05-17 23:17:13', '2025-05-17 23:17:13', 100, 0),
(16, 2, 1, 'Lone Star Light', 'lone-star-light', 'A lighter body and fewer calories than the original, with a clean, crisp taste.', 345.00, 100, 'assets/images/products/lone-star-light.jpg', 4.20, 'Bottle', '330ml', 0, 3, '2025-05-17 23:17:13', '2025-05-17 23:17:13', 100, 0),
(17, 2, 1, 'Lone Star ULTRA', 'lone-star-ultra', 'A low-carb lager designed for health-conscious consumers.', 375.00, 100, 'assets/images/products/lone-star-ultra.jpg', 4.00, 'Can', '330ml', 0, 3, '2025-05-17 23:17:13', '2025-05-17 23:17:13', 100, 0),
(18, 2, 1, 'Colt Ice', 'colt-ice', 'A chilled malt liquor variant with smooth malt sweetness and higher alcohol content.', 375.00, 100, 'assets/images/products/colt-ice.jpg', 7.20, 'Can', '330ml', 0, 4, '2025-05-17 23:17:13', '2025-05-17 23:17:13', 100, 0),
(19, 3, 2, 'Engkanto Pale Ale', 'engkanto-pale-ale', 'Engkanto Pale Ale is a balanced ale with floral hop aromas and a crisp, refreshing finish. It features moderate bitterness complemented by a malt backbone. This beer is a favorite among local craft beer enthusiasts.', 180.00, 100, 'assets/images/products/engkanto-pale-ale.jpg', 5.50, 'Bottle', '330ml', 1, 5, '2025-05-17 23:17:13', '2025-05-17 23:17:13', 100, 0),
(20, 3, 2, 'Engkanto IPA', 'engkanto-ipa', 'Delivers intense hop flavors with citrus and pine notes balanced by malt sweetness.', 175.00, 100, 'assets/images/products/engkanto-ipa.jpg', 6.20, 'Bottle', '330ml', 0, 4, '2025-05-17 23:17:13', '2025-05-17 23:17:13', 100, 0),
(21, 3, 2, 'Engkanto Double IPA', 'engkanto-double-ipa', 'A stronger hop bitterness and higher alcohol content than the standard IPA.', 200.00, 100, 'assets/images/products/engkanto-double-ipa.jpg', 8.00, 'Bottle', '330ml', 0, 4, '2025-05-17 23:17:13', '2025-05-17 23:17:13', 100, 0),
(22, 3, 2, 'Engkanto Mango Ale', 'engkanto-mango-ale', 'Blends ripe mango sweetness with a smooth ale base.', 175.00, 100, 'assets/images/products/engkanto-mango-ale.jpg', 5.00, 'Bottle', '330ml', 0, 4, '2025-05-17 23:17:13', '2025-05-17 23:17:13', 100, 0),
(23, 3, 3, 'Engkanto Wheat Beer', 'engkanto-wheat-beer', 'A light, cloudy wheat ale with banana and clove notes from yeast.', 175.00, 100, 'assets/images/products/engkanto-wheat-beer.jpg', 5.00, 'Bottle', '330ml', 0, 4, '2025-05-17 23:17:13', '2025-05-17 23:17:13', 100, 0),
(24, 3, 4, 'Engkanto Stout', 'engkanto-stout', 'Engkanto Stout is a rich, dark beer with roasted malt flavors of coffee and chocolate. It has a creamy mouthfeel and smooth, slightly bitter finish. Ideal for fans of bold dark beers.', 200.00, 100, 'assets/images/products/engkanto-stout.jpg', 6.00, 'Bottle', '330ml', 0, 4, '2025-05-17 23:17:13', '2025-05-17 23:17:13', 100, 0),
(25, 4, 2, 'Island Hopper IPA', 'island-hopper-ipa', 'Island Hopper IPA bursts with tropical fruit and pine aromas, balanced by a solid malt backbone. It is aromatic and refreshing, perfect for IPA lovers seeking a vibrant local brew. The crisp finish makes it ideal for warm weather.', 220.00, 100, 'assets/images/products/island-hopper-ipa.jpg', 6.50, 'Bottle', '330ml', 1, 5, '2025-05-17 23:17:13', '2025-05-17 23:17:13', 100, 0),
(26, 4, 2, 'Cebuano Pale Ale', 'cebuano-pale-ale', 'Offers a smooth malt profile with citrusy hop character and a balanced bitterness.', 175.00, 100, 'assets/images/products/cebuano-pale-ale.jpg', 5.20, 'Bottle', '330ml', 0, 4, '2025-05-17 23:17:13', '2025-05-17 23:17:13', 100, 0),
(27, 4, 2, 'People Power Pale Ale', 'people-power-pale-ale', 'Medium-bodied with bright hop aromas and a crisp, balanced taste.', 200.00, 100, 'assets/images/products/people-power-pale-ale.jpg', 5.50, 'Bottle', '330ml', 0, 4, '2025-05-17 23:17:13', '2025-05-17 23:17:13', 100, 0),
(28, 4, 2, 'Fat Bottomed Girl', 'fat-bottomed-girl', 'A rich Scotch Ale with caramel and toffee notes.', 245.00, 100, 'assets/images/products/fat-bottomed-girl.jpg', 7.00, 'Bottle', '330ml', 0, 4, '2025-05-17 23:17:13', '2025-05-17 23:17:13', 100, 0),
(29, 5, 2, 'Pedro Pale Ale', 'pedro-pale-ale', 'Pedro Pale Ale is a balanced ale with floral hop aromas and a smooth malt backbone. Crisp and refreshing, it is ideal for everyday drinking. The beer showcases excellent local brewing craftsmanship.', 180.00, 100, 'assets/images/products/pedro-pale-ale.jpg', 5.20, 'Bottle', '330ml', 1, 5, '2025-05-17 23:17:13', '2025-05-17 23:17:13', 100, 0),
(30, 5, 2, 'Pedro Session IPA', 'pedro-session-ipa', 'Bold hop flavors with lower alcohol content for easy drinking.', 175.00, 100, 'assets/images/products/pedro-session-ipa.jpg', 4.50, 'Bottle', '330ml', 0, 4, '2025-05-17 23:17:13', '2025-05-17 23:17:13', 100, 0),
(31, 5, 2, 'Pedro Coffee Ale', 'pedro-coffee-ale', 'Combines rich coffee flavors with a smooth ale base.', 200.00, 100, 'assets/images/products/pedro-coffee-ale.jpg', 5.50, 'Bottle', '330ml', 0, 4, '2025-05-17 23:17:13', '2025-05-17 23:17:13', 100, 0),
(32, 6, 2, 'Crazy Carabao Pale Ale', 'crazy-carabao-pale-ale', 'Crazy Carabao Pale Ale is a vibrant ale with citrus and floral hop notes balanced by malt sweetness. Refreshing and easy to drink, it showcases local craft talent. Perfect for casual social gatherings.', 170.00, 100, 'assets/images/products/   ', 5.50, 'Bottle', '330ml', 0, 4, '2025-05-17 23:17:13', '2025-05-17 23:17:13', 100, 0),
(33, 6, 4, 'Crazy Carabao Stout', 'crazy-carabao-stout', 'Offers roasted malt flavors with hints of chocolate and coffee.', 200.00, 100, 'assets/images/products/crazy-carabao-stout.jpg', 6.00, 'Bottle', '330ml', 0, 4, '2025-05-17 23:17:13', '2025-05-17 23:17:13', 100, 0),
(34, 6, 1, 'Crazy Carabao Pilsner', 'crazy-carabao-pilsner', 'A crisp, clean lager with light malt sweetness and subtle hop bitterness.', 175.00, 100, 'assets/images/products/crazy-carabao-pilsner.jpg', 5.00, 'Bottle', '330ml', 0, 3, '2025-05-17 23:17:13', '2025-05-17 23:17:13', 100, 0),
(35, 7, 2, 'Nipa Pale Ale', 'nipa-pale-ale', 'Nipa Pale Ale features bright hop aromas with citrus and floral notes. Balanced with a malt backbone and clean finish, it is a fresh take on the classic pale ale style. The beer showcases local craft innovation.', 170.00, 100, 'assets/images/products/nipa-pale-ale.jpg', 5.40, 'Bottle', '330ml', 0, 4, '2025-05-17 23:17:13', '2025-05-17 23:17:13', 100, 0),
(36, 7, 1, 'Nipa Hoppy Lager', 'nipa-hoppy-lager', 'Combines the crispness of a lager with aromatic hop character of an ale.', 175.00, 100, 'assets/images/products/nipa-hoppy-lager.jpg', 5.00, 'Bottle', '330ml', 0, 4, '2025-05-17 23:17:13', '2025-05-17 23:17:13', 100, 0),
(37, 8, 4, 'Alamat Ube Stout', 'alamat-ube-stout', 'Alamat Ube Stout is a unique stout brewed with ube (purple yam), lending subtle sweetness and earthy flavor. Rich roasted malt notes and creamy mouthfeel make it a standout. This beer artfully blends Filipino flavors with stout tradition.', 240.00, 100, 'assets/images/products/alamat-ube-stout.jpg', 6.50, 'Bottle', '330ml', 1, 5, '2025-05-17 23:17:13', '2025-05-17 23:17:13', 100, 0),
(38, 8, 1, 'Adlai Rice Lager', 'adlai-rice-lager', 'Adlai Rice Lager is brewed using adlai, a local grain, resulting in a light, crisp lager with a slightly nutty flavor. Refreshing and smooth, it showcases indigenous ingredients. The beer is perfect for those seeking distinct Filipino craft brews.', 190.00, 100, 'assets/images/products/adlai-rice-lager.jpg', 5.00, 'Bottle', '330ml', 0, 4, '2025-05-17 23:17:13', '2025-05-17 23:17:13', 100, 0),
(39, 4, 2, 'Fat Pauly\'s IPA', 'fat-paulys-ipa', 'Fat Pauly\'s IPA is a hop-forward ale with citrus and pine aromas balanced by malt sweetness. Bold and aromatic, it appeals to craft beer lovers. The beer finishes crisp and refreshing.', 200.00, 100, 'assets/images/products/fat-paulys-ipa.jpg', 6.50, 'Bottle', '330ml', 0, 4, '2025-05-17 23:17:13', '2025-05-17 23:17:13', 100, 0); 