<?php
namespace ZfcDatagrid\Column\Action;

class Button extends AbstractAction
{

    protected $label = '';

    public function __construct ()
    {
        $this->htmlAttributes['class'] = array(
            'btn'
        );
    }

    /**
     *
     * @param string $name            
     */
    public function setLabel ($name)
    {
        $this->label = (string) $name;
    }

    /**
     *
     * @return string
     */
    public function getLabel ()
    {
        return $this->label;
    }

    /**
     *
     * @return string
     */
    public function toHtml ()
    {
        $attributes = array();
        foreach ($this->getAttributes() as $attrKey => $attrValue) {
            if (is_array($attrValue)) {
                $attrValue = implode(' ', $attrValue);
            }
            $attributes[] = $attrKey . '="' . $attrValue . '"';
        }
        
        $attributes = implode(' ', $attributes);
        
        return '<a href="' . $this->getLink() . '" ' . $attributes . '>' . $this->getLabel() . '</a>';
    }
}