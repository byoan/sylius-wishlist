<?php

declare(strict_types=1);

namespace Byoan\SyliusWishlistPlugin\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Sylius\Bundle\CoreBundle\Doctrine\Migrations\AbstractMigration;

final class Version20261104143519 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create the wishlist and wishlist item tables.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE byoan_wishlist (name VARCHAR(255) NOT NULL, is_default TINYINT DEFAULT NULL, token VARCHAR(64) DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, id INT AUTO_INCREMENT NOT NULL, customer_id INT DEFAULT NULL, channel_id INT NOT NULL, INDEX IDX_D1F3B1929395C3F3 (customer_id), INDEX IDX_D1F3B19272F5A1AA (channel_id), UNIQUE INDEX byoan_wishlist_customer_channel_default_unique (customer_id, channel_id, is_default), UNIQUE INDEX byoan_wishlist_customer_channel_name_unique (customer_id, channel_id, name), UNIQUE INDEX byoan_wishlist_token_channel_default_unique (token, channel_id, is_default), UNIQUE INDEX byoan_wishlist_token_channel_name_unique (token, channel_id, name), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE byoan_wishlist_item (created_at DATETIME NOT NULL, id INT AUTO_INCREMENT NOT NULL, wishlist_id INT NOT NULL, variant_id INT NOT NULL, INDEX IDX_6397315DFB8E54CD (wishlist_id), INDEX IDX_6397315D3B69A9AF (variant_id), UNIQUE INDEX byoan_wishlist_item_wishlist_variant_unique (wishlist_id, variant_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE byoan_wishlist ADD CONSTRAINT FK_D1F3B1929395C3F3 FOREIGN KEY (customer_id) REFERENCES sylius_customer (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE byoan_wishlist ADD CONSTRAINT FK_D1F3B19272F5A1AA FOREIGN KEY (channel_id) REFERENCES sylius_channel (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE byoan_wishlist_item ADD CONSTRAINT FK_6397315DFB8E54CD FOREIGN KEY (wishlist_id) REFERENCES byoan_wishlist (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE byoan_wishlist_item ADD CONSTRAINT FK_6397315D3B69A9AF FOREIGN KEY (variant_id) REFERENCES sylius_product_variant (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE byoan_wishlist DROP FOREIGN KEY FK_D1F3B1929395C3F3');
        $this->addSql('ALTER TABLE byoan_wishlist DROP FOREIGN KEY FK_D1F3B19272F5A1AA');
        $this->addSql('ALTER TABLE byoan_wishlist_item DROP FOREIGN KEY FK_6397315DFB8E54CD');
        $this->addSql('ALTER TABLE byoan_wishlist_item DROP FOREIGN KEY FK_6397315D3B69A9AF');
        $this->addSql('DROP TABLE byoan_wishlist');
        $this->addSql('DROP TABLE byoan_wishlist_item');
    }
}
