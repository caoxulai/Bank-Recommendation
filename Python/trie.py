def c_to_i(c):
    if c == " ":
        return 26
    elif ord('a') <= ord(c) and ord(c) <= ord('z'):
        return ord(c) - ord('a')
    else:
        return 27


class TrieNode:
    def __init__(self):
        self.cnt = 0
        self.index = -1
        self.cnt_appear = -1
        self.children = list(range(28))
        for i in range(28):
            self.children[i] = None


class Trie:
    def __init__(self):
        self.tot_word = 0
        self.root = TrieNode()

    def insert_word(self, st):
        current = self.root
        for i in range(len(st)):
            ind = c_to_i(st[i])
            if current.children[ind] is None:
                tmp = TrieNode()
                current.children[ind] = tmp
                current = tmp
                if i == len(st) - 1:
                    tmp.index = self.tot_word
                    self.tot_word += 1
                    tmp.cnt_appear = 1
            else:
                current = current.children[ind]
                if i == len(st) - 1:
                    if current.index == -1:
                        current.index = self.tot_word
                        self.tot_word += 1
                        current.cnt_appear = 1
                    else:
                        current.cnt_appear += 1

    def find_word(self, st):
        current = self.root
        for i in range(len(st)):
            ind = c_to_i(st[i])
            if current.children[ind] is None:
                return None
            else:
                current = current.children[ind]
        return current

    def dfs(self, current, threshold):
        if current.index != -1 and current.cnt_appear > threshold:
            self.cnt += 1
        for i in range(28):
            if current.children[i] is not None:
                self.dfs(current.children[i], threshold)

    def num_above_threshold(self, threshold=1):
        self.cnt = 0
        self.dfs(self.root, threshold)
        return self.cnt