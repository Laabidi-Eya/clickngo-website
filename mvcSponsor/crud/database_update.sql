-- Add payment_code column to sponsor2 table
ALTER TABLE sponsor2
ADD COLUMN payment_code VARCHAR(255) NULL;
