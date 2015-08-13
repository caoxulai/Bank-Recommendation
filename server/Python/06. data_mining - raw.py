__author__ = 'nathan'

import re
import csv
from nltk.stem.lancaster import LancasterStemmer
import pickle
import random
import json
from pprint import pprint

import timeit

# Initiate timer
start = timeit.default_timer()


# ################# Preprocessing functions start here ##################
# Example: goooood -> god   tweet->twet
def replaceTwoOrMore(s):
    pattern = re.compile(r"(.)\1{1,}", re.DOTALL)
    return pattern.sub(r"\1\1", s)


# start process_text
def processText(text):
    #  process the texts

    # Convert to lower case
    text = text.lower()
    # Convert www.* or https?://* to URL
    text = re.sub('((www\.[^\s]+)|(https?://[^\s]+))', ' ', text)
    # Convert @username to AT_USER
    text = re.sub('@[^\s]+', ' ', text)
    # Remove additional white spaces
    text = re.sub('[\s]+', ' ', text)
    # Replace # word with word
    text = re.sub(r'# ([^\s]+)', r'\1', text)
    # remove quot,&amp; &gt; &lt;
    text = re.sub(r'&quot;', '', text)
    text = re.sub(r'&amp', '', text)
    text = re.sub(r'&gt;', '', text)
    text = re.sub(r'&lt;', '', text)
    # remove extra symbols
    text = re.sub('[\']', '', text)
    text = re.sub('[\+\=\.\,\~\{\}\^\;\:\*\)\(\[\]\!\-\n\?]', ' ', text)
    text = re.sub('[\\\/]', ' ', text)

    text = text.replace("\\", " ")

    # trim
    text = text.strip('\'"')
    return text

# end


# start getStopWordList
def getStopWordList(stopWordListFileName):
    # read the stopwords
    stopWords = []
    stopWords.append('AT_USER')
    stopWords.append('URL')

    fp = open(stopWordListFileName, 'r')
    line = fp.readline()
    while line:
        word = line.strip()
        stopWords.append(word)
        line = fp.readline()
    fp.close()
    return stopWords

# end


# start getfeatureVector
def getFeatureVector(text, stopWords):
    featureVector = []
    words = text.split()
    # print(text)
    # words = re.split(r' ',text)
    # print(words)
    for w in words:
        # replace two or more with two occurrences
        w = replaceTwoOrMore(w)
        # strip punctuation
        w = w.strip('\'"?,.!')
        w = w.lower()
        # check if it consists of only words
        # val = re.search(r"^[a-zA-Z][a-zA-Z0-9]*[a-zA-Z]+[a-zA-Z0-9]*$", w)
        val = re.search(r"[a-zA-Z]+$", w)
        val_num = re.search(r"[a-zA-Z]*[0-9]+[a-zA-Z]*$", w)

        if len(w) <= 1:
            continue

        if w in stopWords:
            continue

        if (not val_num is None):
            continue
        # ignore if it is a stopWord
        # if(w in stopWords or val is None):
        if (val is None):
            # print(w)
            continue
        else:
            # print(w,' '),
            featureVector.append(w)
    return featureVector

# ################# Preprocessing starts here ##################
with open('merged_data.json') as data_file:
    data = json.load(data_file)
# pprint(data)

# for stemming algorithm, for dimension reduction
st = LancasterStemmer()

stopWords = getStopWordList('StopwordsList.txt')
# pprint(stopWords)


# featureList = []
processed_data = []
# print(inpTweets)
print('Processing Text')

for node in data:
    processed_n = processText(node['name'])
    featureVector_n = getFeatureVector(processed_n, stopWords)
    featureVector_n = [st.stem(e) for e in featureVector_n]

    processed_d = processText(node['description'])
    featureVector_d = getFeatureVector(processed_d, stopWords)
    featureVector_d = [st.stem(e) for e in featureVector_d]

    new_node = {}
    new_node['ori_label'] = node['ori_label']  # to be deleted
    new_node['name'] = node['name']
    new_node['fea_n'] = featureVector_n
    new_node['fea_d'] = featureVector_d

    processed_data.append(new_node)


# Write to Json file, file content: feature list separated by comma
# with open('processed_data.json', 'w') as outfile:
#     json.dump(processed_data, outfile)


# ################# Generate sparse feature list ##################
import cPickle as pickle
from auxiliary_functions import *
from trie import *
import json
from pprint import pprint


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


# with open('processed_data.json') as data_file:
#     processed_data = json.load(data_file)
#
# dictionary = create_dictionary(processed_data, 1)
# with open('dictionary.pkl', 'wb') as output:
#     pickle.dump(dictionary, output, pickle.HIGHEST_PROTOCOL)

with open('dictionary.pkl', 'rb') as input:
    dic_file = pickle.load(input)
create_sparse_matrix(dic_file, processed_data, 1, 4, "raw_label_feature")
print("Input file has been created...")


# ################# Read model & Classification ##################
from numpy import genfromtxt


def construct_training_set(label_after_file_name, data_file_name, target_file_name):

    label_after = genfromtxt(label_after_file_name, delimiter=',')
    f = open(target_file_name, 'w')

    i = 0
    for line in open(data_file_name):
        s = str(int(label_after[i])) + " "

        line = line.split(None, 1)
        # In case an instance with all zero features
        if len(line) == 1: line += ['']
        label, features = line
        s += features
        f.write(s)
        i += 1


construct_training_set('label_after.csv', 'raw_label_feature', 'label_feature')

# svm classifier using libsvm
fr = 8
to = 10

# import sklearn.svm.libsvm as svm
from svmutil import *
y, x = svm_read_problem('label_feature')
print("Problem has been read...")

# m = svm_train(y[fr:to], x[fr:to], '-s 1 -h 0 -m 1000')
# mt = svm_train(y, x, '-s 0 -h 0 -m 1000')
# print("Model get trained successfully...")
# #p_label, p_acc, p_val = svm_predict(y[0:1000] + y[59000:60000], x[0:1000] + x[59000:60000], m)
#
# svm_save_model('svm_model', mt)
m = svm_load_model('svm_model')


# svm_predict(y[fr:to], x[fr:to], m)
yt = y[fr:to] + y[500:505] + y[1500:1502] + y[900:902] + y[2000:2002]
xt = x[fr:to] + x[500:505] + x[1500:1502] + x[900:902] + x[2000:2002]


p_label, p_acc, p_val = svm_predict(yt, xt, m)
print(p_acc)
for i in range(0, len(p_label)):
    print(i, p_label[i], p_val[i])
    print('\n')




#Your statements here
stop = timeit.default_timer()

print "Time: " + "{0:.2f}".format(stop - start) + "s"
