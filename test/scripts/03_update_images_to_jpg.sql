-- Update all product images from SVG to JPG format
-- This script updates the existing products to use JPG images instead of SVG

USE oscilloscope_catalog;

-- Update all product image URLs from SVG to JPG format
UPDATE products SET image_url = '/public/placeholder.jpg' WHERE image_url LIKE '%placeholder.svg%';
