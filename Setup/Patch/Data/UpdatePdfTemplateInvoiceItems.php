<?php
namespace Eadesigndev\Pdfgenerator\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;
use Eadesigndev\Pdfgenerator\Model\PdfgeneratorFactory as TemplateFactory;
use Eadesigndev\Pdfgenerator\Model\ResourceModel\Pdfgenerator as TemplateResource;

class UpdatePdfTemplateInvoiceItems implements DataPatchInterface
{
    /**
     * @param TemplateFactory $templateFactory
     * @param TemplateResource $templateResource
     */
    public function __construct(
        private TemplateFactory $templateFactory,
        private TemplateResource $templateResource,
    ) {
    }

    /**
     * @return UpdatePdfTemplateInvoiceItems|void
     */
    public function apply()
    {
        /** @var \Eadesigndev\Pdfgenerator\Model\Pdfgenerator $template */
        $template = $this->templateFactory->create();
        $this->templateResource->load($template, 'Invoice VaxLtd', 'template_name');
        if ($template->getId()) {
            $templateBody = $template->getTemplateBody();
            $templateBody = str_replace(
                '{{var order.getPayment().getMethodInstance().getTitle()}}',
                '{{var payment_html | raw}}',
                $templateBody
            );
            $templateBody = str_replace(
                '{{layout area="frontend" handle="vax_sales_email_order_invoice_items" invoice=$invoice order=$order}}',
                '{{layout handle="vax_sales_email_order_invoice_items" invoice_id=$invoice_id order_id=$order_id}}',
                $templateBody
            );
            $template->setData(
                'template_body',
                $templateBody
            );
            $this->templateResource->save($template);
        }
    }

    /**
     * @return array|string[]
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * @return array|string[]
     */
    public static function getDependencies()
    {
        return [];
    }
}
