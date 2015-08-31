__author__ = 'nathan'

import json
import timeit

# Initiate tiemr
start = timeit.default_timer()

merged_data = []

with open('apartment_sale.json') as data_file:
    data = json.load(data_file)
    for node in data:
        merged_data.append(node)
#
with open('banking.json') as data_file:
    data = json.load(data_file)
    for node in data:
        merged_data.append(node)

with open('credit_card.json') as data_file:
    data = json.load(data_file)
    for node in data:
        merged_data.append(node)

with open('credit.json') as data_file:
    data = json.load(data_file)
    for node in data:
        merged_data.append(node)

with open('finance.json') as data_file:
    data = json.load(data_file)
    for node in data:
        merged_data.append(node)

with open('house_sale.json') as data_file:
    data = json.load(data_file)
    for node in data:
        merged_data.append(node)

with open('travel_insurance.json') as data_file:
    data = json.load(data_file)
    for node in data:
        merged_data.append(node)

with open('invest.json') as data_file:
    data = json.load(data_file)
    for node in data:
        merged_data.append(node)

with open('mortgage.json') as data_file:
    data = json.load(data_file)
    for node in data:
        merged_data.append(node)

with open('saving.json') as data_file:
    data = json.load(data_file)
    for node in data:
        merged_data.append(node)

with open('travel.json') as data_file:
    data = json.load(data_file)
    for node in data:
        merged_data.append(node)

with open('vacation.json') as data_file:
    data = json.load(data_file)
    for node in data:
        merged_data.append(node)

with open('wealth.json') as data_file:
    data = json.load(data_file)
    for node in data:
        merged_data.append(node)


# Write merged data into file
with open('merged_data.json', 'w') as outfile:
    json.dump(merged_data, outfile)


# Test
# with open('merged_data.json') as data_file:
#     merged_data_read = json.load(data_file)
#
# for i in range(0, len(merged_data_read)):
#     print(i, merged_data_read[i])
#     print('\n')

#Your statements here
stop = timeit.default_timer()

print "Time: " + "{0:.2f}".format(stop - start) + "s"
