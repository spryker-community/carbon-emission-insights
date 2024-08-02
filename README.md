# Hackathon Project
Created by team EcoMinds

## Short Project Description
Integrating Carbon emission metrics and providing product alternatives.

Our project focuses on enhancing environmental transparency by integrating carbon footprint data into the shopping experience. On the product detail page, users can view the carbon footprint associated with each product, providing insights into its environmental impact. In the cart, we display estimated carbon emissions based on the product's origin and the distance to the delivery location, encouraging users to select shipping options that minimize carbon emissions. Additionally, our product listing page (PLP) features labels highlighting products with lower carbon footprints, making it easier for users to choose environmentally friendly options. This approach aims to promote sustainability and inform consumers about their choices' environmental impact.

## HowTos Cli
for adding attribute: docker/sdk console data:import:product-attribute-key && docker/sdk console data:import:product-management-attribute
 
for glossary update:  docker/sdk console data:import:glossary
 
for yves build : docker/sdk console frontend:yves:build && docker/sdk console backend:yves:build
 
## API reference
For generating key for the API we are using https://www.climatiq.io/
 
## How the attribute will work
 
First go to Manage Attributes for any Product Abstract and then save.
Then we have the attributes of carbon_emission and energy_emission of that product.