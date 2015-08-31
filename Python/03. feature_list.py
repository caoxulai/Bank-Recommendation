import cPickle as pickle
from auxiliary_functions import *
from trie import *
import json
from pprint import pprint
import timeit

# Initiate tiemr
start = timeit.default_timer()

def create_dictionary(processed_data_array, granularity):
    dic = Trie()

    # To build up the dictionary
    for i in range(len(processed_data_array)):
        sentence = processed_data_array[i]['fea_n']
        if granularity == 1:
            gram_sentence = sentence
        else:
            gram_sentence = create_bigram(sentence)
        for gram_word in gram_sentence:
            dic.insert_word(gram_word)

        sentence = processed_data_array[i]['fea_d']
        if granularity == 1:
            gram_sentence = sentence
        else:
            gram_sentence = create_bigram(sentence)
        for gram_word in gram_sentence:
            dic.insert_word(gram_word)
    return dic


def create_sparse_matrix(dic, processed_data_array, granularity, threshold, filename):

    # Transform to a Sparse Feature List
    f = open(filename, 'w')

    count = 0

    for i in range(len(processed_data_array)):
        sparse_vector = {}
        label = processed_data_array[i]['ori_label']

        sentence = processed_data_array[i]['fea_d']
        if granularity == 1:
            gram_sentence = sentence
        else:
            gram_sentence = create_bigram(sentence)
        no_description = 1
        for gram_word in gram_sentence:
            node = dic.find_word(gram_word)
            if node is not None and node.cnt_appear > threshold:
                count += 1
                index = node.index
                no_description = 0
                if index in sparse_vector:
                    sparse_vector[index] += 1
                else:
                    sparse_vector[index] = 1
        if no_description == 1:
            continue

        sentence = processed_data_array[i]['fea_n']
        if granularity == 1:
            gram_sentence = sentence
        else:
            gram_sentence = create_bigram(sentence)
        for gram_word in gram_sentence:
            node = dic.find_word(gram_word)
            if node is not None and node.cnt_appear > threshold:

                count += 1
                index = node.index
                if index in sparse_vector:
                    sparse_vector[index] += 2
                else:
                    sparse_vector[index] = 2

        s = str(label) + " "
        total_feature = 0
        for k, v in sparse_vector.items():
            total_feature += v
        for k, v in sparse_vector.items():
            s += str(k) + ":" + str(int(float(str(v))/total_feature*1000)) + " "
        s += "\n"
        f.write(s)
    print "valid feature: " + str(count)

        #print(label)
        #print(sparse_vector, end=' ')



# with open("preprocessed_tweets_gu.pickle", 'rb') as f: #load the tmp file(avoid reading database everytime)
#     inptweets = pickle.load(f)

with open('processed_data.json') as data_file:
    processed_data = json.load(data_file)

dictionary = create_dictionary(processed_data, 1)
with open('dictionary.pkl', 'wb') as output:
    pickle.dump(dictionary, output, pickle.HIGHEST_PROTOCOL)

with open('dictionary.pkl', 'rb') as input:
    dic_file = pickle.load(input)
create_sparse_matrix(dic_file, processed_data, 1, 4, "raw_label_feature")
print("Input file has been created...")


# fp1 = open("credit_card_feature - 1", 'r')
# fp = open("credit_card_feature", 'r')
# line1 = fp1.readline()
# line = fp.readline()
# i = 0
# while line:
#     print(i, line1)
#     print(i, line)
#     print('\n')
#     i+=1
#     line1 = fp1.readline()
#     line = fp.readline()
#
# fp1.close()
# fp.close()

# fp = open("label_feature_list", 'r')
# line = fp.readline()
# i = 0
# while line:
#     print(i, processed_data[i])
#     print(i, line)
#     print('\n')
#     i+=1
#     line = fp.readline()
#
# fp.close()





#
# print(flist)
# print('\n')

# i = 0
# for line in flist.splitlines():
#     print(i, processed_data[i])
#     print(i, line)
#     print('\n')
#     i++
#
# flist = flist.splitlines()
# print(flist)
# print('\n')
#
# for i in range(len(processed_data)):
#     print(i, processed_data[i])
#     print(i, flist[i])
#     print('\n')


"""
#svm classifier using libsvm
fr = 25000
to = 35000

from svmutil import *
y, x = svm_read_problem('svm_input2')
print("Problem has been read...")
m = svm_train(y[fr:to], x[fr:to], '-s 1 -h 0 -m 1000')
#m = svm_train(y, x, '-s 1 -h 0 -m 1000')
print("Model get trained successfully...")
#p_label, p_acc, p_val = svm_predict(y[0:1000] + y[59000:60000], x[0:1000] + x[59000:60000], m)
p_label, p_acc, p_val = svm_predict(y[fr:to], x[fr:to], m)
#p_label, p_acc, p_val = svm_predict(y, x, m)
"""

# from sklearn.datasets import load_svmlight_file
# from sklearn.naive_bayes import GaussianNB
# x_train, y_train = load_svmlight_file('svm_input')
# gnb = GaussianNB()
# y_pred = gnb.fit(x_train, y_train).predict(x_train)
# print(y_pred)

#Your statements here
stop = timeit.default_timer()

print "Time: " + "{0:.2f}".format(stop - start) + "s"
