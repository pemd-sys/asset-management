-- Seed data for oscilloscope catalog
USE oscilloscope_catalog;

-- Insert brands
INSERT INTO brands (name, logo_url) VALUES
('Tektronix', '/images/brands/tektronix.png'),
('Keysight', '/images/brands/keysight.png'),
('Rigol', '/images/brands/rigol.png'),
('Siglent', '/images/brands/siglent.png'),
('Hantek', '/images/brands/hantek.png'),
('Owon', '/images/brands/owon.png'),
('Rohde & Schwarz', '/images/brands/rohde-schwarz.png'),
('LeCroy', '/images/brands/lecroy.png');

-- Insert categories
INSERT INTO categories (name, description) VALUES
('Digital Storage Oscilloscopes', 'Digital storage oscilloscopes for general purpose measurements'),
('Mixed Signal Oscilloscopes', 'Oscilloscopes with both analog and digital channel capabilities'),
('High Bandwidth Oscilloscopes', 'High-performance oscilloscopes for advanced applications'),
('Portable Oscilloscopes', 'Compact and portable oscilloscope solutions');

-- Insert products
INSERT INTO products (name, model, description, brand_id, category_id, price, original_price, image_url, stock_status, stock_quantity, rating, review_count, bandwidth, channels, sample_rate, is_featured, is_on_sale) VALUES
-- Tektronix products
('Tektronix TBS1052C', 'TBS1052C', '50 MHz, 2-Channel Digital Storage Oscilloscope', 1, 1, 389.99, 429.99, '/placeholder.svg?height=200&width=200', 'in_stock', 25, 4.5, 12, '50 MHz', 2, '1 GS/s', TRUE, TRUE),
('Tektronix TBS1104', 'TBS1104', '100 MHz, 4-Channel Digital Storage Oscilloscope', 1, 1, 649.99, NULL, '/placeholder.svg?height=200&width=200', 'in_stock', 18, 4.6, 8, '100 MHz', 4, '2 GS/s', FALSE, FALSE),
('Tektronix MSO2024B', 'MSO2024B', '200 MHz, 4+16 Channel Mixed Signal Oscilloscope', 1, 2, 2499.99, NULL, '/placeholder.svg?height=200&width=200', 'in_stock', 5, 4.8, 15, '200 MHz', 4, '1 GS/s', TRUE, FALSE),

-- Keysight products
('Keysight DSOX1204G', 'DSOX1204G', '200 MHz, 4-Channel Digital Storage Oscilloscope', 2, 1, 1249.99, NULL, '/placeholder.svg?height=200&width=200', 'in_stock', 12, 4.7, 8, '200 MHz', 4, '2 GS/s', FALSE, FALSE),
('Keysight DSOX3024T', 'DSOX3024T', '200 MHz, 4-Channel Oscilloscope with Touch Screen', 2, 1, 3299.99, 3599.99, '/placeholder.svg?height=200&width=200', 'in_stock', 8, 4.9, 22, '200 MHz', 4, '5 GS/s', TRUE, TRUE),
('Keysight MSOX3104T', 'MSOX3104T', '1 GHz, 4+16 Channel Mixed Signal Oscilloscope', 2, 2, 8999.99, NULL, '/placeholder.svg?height=200&width=200', 'low_stock', 3, 4.8, 11, '1 GHz', 4, '5 GS/s', TRUE, FALSE),

-- Rigol products
('Rigol DS1054Z', 'DS1054Z', '50 MHz, 4-Channel Digital Storage Oscilloscope', 3, 1, 299.99, 349.99, '/placeholder.svg?height=200&width=200', 'low_stock', 7, 4.3, 15, '50 MHz', 4, '1 GS/s', FALSE, TRUE),
('Rigol DS1104Z', 'DS1104Z', '100 MHz, 4-Channel Digital Storage Oscilloscope', 3, 1, 449.99, NULL, '/placeholder.svg?height=200&width=200', 'in_stock', 20, 4.4, 18, '100 MHz', 4, '1 GS/s', FALSE, FALSE),
('Rigol MSO5074', 'MSO5074', '70 MHz, 4+16 Channel Mixed Signal Oscilloscope', 3, 2, 1899.99, NULL, '/placeholder.svg?height=200&width=200', 'in_stock', 6, 4.6, 9, '70 MHz', 4, '8 GS/s', FALSE, FALSE),

-- Siglent products
('Siglent SDS1204X-E', 'SDS1204X-E', '200 MHz, 4-Channel Digital Storage Oscilloscope', 4, 1, 449.99, NULL, '/placeholder.svg?height=200&width=200', 'in_stock', 15, 4.6, 9, '200 MHz', 4, '1 GS/s', FALSE, FALSE),
('Siglent SDS2304X', 'SDS2304X', '300 MHz, 4-Channel Super Phosphor Oscilloscope', 4, 1, 1299.99, 1399.99, '/placeholder.svg?height=200&width=200', 'in_stock', 10, 4.7, 12, '300 MHz', 4, '2 GS/s', FALSE, TRUE),
('Siglent SDS5034X', 'SDS5034X', '350 MHz, 4-Channel High Definition Oscilloscope', 4, 3, 4999.99, NULL, '/placeholder.svg?height=200&width=200', 'low_stock', 2, 4.9, 7, '350 MHz', 4, '5 GS/s', TRUE, FALSE),

-- Hantek products
('Hantek DSO5102P', 'DSO5102P', '100 MHz, 2-Channel Digital Storage Oscilloscope', 5, 1, 189.99, 219.99, '/placeholder.svg?height=200&width=200', 'in_stock', 30, 4.1, 6, '100 MHz', 2, '1 GS/s', FALSE, TRUE),
('Hantek DSO5202P', 'DSO5202P', '200 MHz, 2-Channel Digital Storage Oscilloscope', 5, 1, 299.99, NULL, '/placeholder.svg?height=200&width=200', 'in_stock', 22, 4.2, 11, '200 MHz', 2, '1 GS/s', FALSE, FALSE),
('Hantek 6254BD', '6254BD', '250 MHz, 4-Channel Portable Oscilloscope', 5, 4, 899.99, 999.99, '/placeholder.svg?height=200&width=200', 'in_stock', 8, 4.3, 5, '250 MHz', 4, '1 GS/s', FALSE, TRUE),

-- Owon products
('Owon SDS1102', 'SDS1102', '100 MHz, 2-Channel Digital Storage Oscilloscope', 6, 1, 229.99, NULL, '/placeholder.svg?height=200&width=200', 'out_of_stock', 0, 4.0, 4, '100 MHz', 2, '1 GS/s', FALSE, FALSE),
('Owon SDS7102V', 'SDS7102V', '100 MHz, 2-Channel Deep Memory Oscilloscope', 6, 1, 399.99, 449.99, '/placeholder.svg?height=200&width=200', 'low_stock', 4, 4.1, 8, '100 MHz', 2, '1 GS/s', FALSE, TRUE),
('Owon XDS3104AE', 'XDS3104AE', '100 MHz, 4-Channel Touchscreen Oscilloscope', 6, 1, 799.99, NULL, '/placeholder.svg?height=200&width=200', 'in_stock', 12, 4.4, 6, '100 MHz', 4, '1 GS/s', FALSE, FALSE),

-- Rohde & Schwarz products
('R&S RTB2004', 'RTB2004', '70 MHz, 4-Channel Oscilloscope', 7, 1, 2199.99, NULL, '/placeholder.svg?height=200&width=200', 'in_stock', 6, 4.8, 14, '70 MHz', 4, '2.5 GS/s', TRUE, FALSE),
('R&S RTO2024', 'RTO2024', '2 GHz, 4-Channel High-End Oscilloscope', 7, 3, 24999.99, NULL, '/placeholder.svg?height=200&width=200', 'low_stock', 1, 4.9, 8, '2 GHz', 4, '20 GS/s', TRUE, FALSE),

-- LeCroy products
('LeCroy WaveAce 2024', 'WaveAce 2024', '200 MHz, 4-Channel Oscilloscope', 8, 1, 1899.99, 2099.99, '/placeholder.svg?height=200&width=200', 'in_stock', 9, 4.6, 10, '200 MHz', 4, '2 GS/s', FALSE, TRUE),
('LeCroy WaveSurfer 3024z', 'WaveSurfer 3024z', '200 MHz, 4-Channel HD Oscilloscope', 8, 1, 4999.99, NULL, '/placeholder.svg?height=200&width=200', 'in_stock', 4, 4.7, 12, '200 MHz', 4, '4 GS/s', TRUE, FALSE);

-- Insert additional product specifications
INSERT INTO product_specifications (product_id, spec_name, spec_value) VALUES
-- Tektronix TBS1052C specs
(1, 'Memory Depth', '2.5k points'),
(1, 'Waveform Capture Rate', '5,000 wfms/s'),
(1, 'Display', '7" WVGA'),
(1, 'Connectivity', 'USB'),

-- Keysight DSOX1204G specs
(4, 'Memory Depth', '1 Mpts'),
(4, 'Waveform Update Rate', '50,000 wfms/s'),
(4, 'Display', '8.5" WVGA'),
(4, 'Connectivity', 'USB, Ethernet, VGA'),

-- Rigol DS1054Z specs
(7, 'Memory Depth', '12 Mpts'),
(7, 'Waveform Capture Rate', '30,000 wfms/s'),
(7, 'Display', '7" WVGA'),
(7, 'Connectivity', 'USB, Ethernet'),

-- Add more specifications for other products as needed
(10, 'Memory Depth', '14 Mpts'),
(10, 'Display', '8" Capacitive Touch'),
(10, 'Connectivity', 'USB, Ethernet, WiFi');
